<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Genkgo\Mail\Exception\ConnectionRefusedException;

/**
 * Class TlsConnection
 * @package Genkgo\Mail\Protocol
 * @codeCoverageIgnore
 */
final class TlsConnection extends AbstractConnection
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
        $resource = @stream_socket_client(
            'tls://' . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->options->getTimeout()
        );

        if ($resource === false) {
            throw new ConnectionRefusedException(
                sprintf('Could not create tls connection. %s.', $errorMessage),
                $errorCode
            );
        }

        $this->resource = $resource;
        $this->fireEvent('connect');
    }

    /**
     * @param resource $resource
     * @param string $host
     * @param int $port
     * @return TlsConnection
     */
    public static function fromResource(
        $resource,
        string $host,
        int $port
    ): TlsConnection
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Expecting resource');
        }

        $connection = new self($host, $port, new SecureConnectionOptions());
        $connection->resource = $resource;
        return $connection;
    }
}