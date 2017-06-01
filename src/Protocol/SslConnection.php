<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

/**
 * Class SslConnection
 * @package Genkgo\Mail\Protocol
 * @codeCoverageIgnore
 */
final class SslConnection extends AbstractConnection
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
        throw new \InvalidArgumentException('Cannot upgrade TLS connection, already encrypted');
    }

    /**
     *
     */
    public function connect(): void
    {
        $this->resource = @stream_socket_client(
            'ssl://' . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->options->getTimeout()
        );

        if ($this->resource === false) {
            throw new \RuntimeException(sprintf(
                'Could not create resource: %s', $errorMessage), $errorCode
            );
        }
    }
}