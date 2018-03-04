<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Genkgo\Mail\Exception\ConnectionRefusedException;

/**
 * Class SecureConnection
 * @package Genkgo\Mail\Protocol
 * @codeCoverageIgnore
 */
final class SecureConnection extends AbstractConnection
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var SecureConnectionOptions
     */
    private $options;

    /**
     * PlainTcpConnection constructor.
     * @param string $host
     * @param int $port
     * @param SecureConnectionOptions $options
     */
    public function __construct(string $host, int $port, SecureConnectionOptions $options)
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
    }

    /**
     * @param int $type
     */
    public function upgrade(int $type): void
    {
        throw new \InvalidArgumentException('Cannot connection, already secure');
    }

    /**
     *
     */
    public function connect(): void
    {
        $context = \stream_context_create([
            'ssl' => [
                'crypto_method' => $this->options->getMethod(),
            ]
        ]);

        $resource = @\stream_socket_client(
            'tls://' . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->options->getTimeout(),
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($resource === false) {
            throw new ConnectionRefusedException(
                \sprintf('Could not create secure connection. %s.', $errorMessage),
                $errorCode
            );
        }

        $this->resource = $resource;
        $this->fireEvent('connect');
    }
}
