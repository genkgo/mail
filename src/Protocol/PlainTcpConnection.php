<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Genkgo\Mail\Exception\ConnectionRefusedException;

/**
 * @codeCoverageIgnore
 */
final class PlainTcpConnection extends AbstractConnection
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
     * @var float
     */
    private $connectionTimeout;

    /**
     * @param string $host
     * @param int $port
     * @param float $connectionTimeout
     */
    public function __construct(string $host, int $port, float $connectionTimeout = 1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @param int $type
     */
    public function upgrade(int $type): void
    {
        if (\stream_socket_enable_crypto($this->resource, true, $type) === false) {
            throw new \InvalidArgumentException('Cannot upgrade connection to requested encryption type');
        }
    }
    
    public function connect(): void
    {
        $resource = @\stream_socket_client(
            'tcp://' . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->connectionTimeout
        );

        if ($resource === false) {
            throw new ConnectionRefusedException(
                \sprintf('Could not create plain tcp connection. %s.', $errorMessage),
                $errorCode
            );
        }

        $this->resource = $resource;
        $this->fireEvent('connect');
    }
}
