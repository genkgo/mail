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
    private $protocol;
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
     * @param string $protocol
     * @param string $host
     * @param int $port
     * @param SecureConnectionOptions $options
     */
    public function __construct(string $protocol, string $host, int $port, SecureConnectionOptions $options)
    {
        $this->protocol = $protocol;
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
        $resource = @stream_socket_client(
            $this->protocol . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->options->getTimeout()
        );

        if ($resource === false) {
            throw new ConnectionRefusedException(
                sprintf('Could not create secure connection. %s.', $errorMessage),
                $errorCode
            );
        }

        $this->resource = $resource;
        $this->fireEvent('connect');
    }
}