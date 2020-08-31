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
     * @var array<string, array<string, mixed>>
     */
    private $contextOptions;

    /**
     * @param string $host
     * @param int $port
     * @param float $connectionTimeout
     * @param array<string, array<string, mixed>> $contextOptions
     */
    public function __construct(string $host, int $port, float $connectionTimeout = 1.0, array $contextOptions = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
        $this->contextOptions = $contextOptions;
    }

    /**
     * @param int $type
     */
    public function upgrade(int $type): void
    {
        if ($this->resource === null || \stream_socket_enable_crypto($this->resource, true, $type) === false) {
            throw new \InvalidArgumentException('Cannot upgrade connection, resource not available');
        }
    }
    
    public function connect(): void
    {
        $context = \stream_context_create($this->contextOptions);

        $resource = @\stream_socket_client(
            'tcp://' . $this->host . ':' . $this->port,
            $errorCode,
            $errorMessage,
            $this->connectionTimeout,
            \STREAM_CLIENT_CONNECT,
            $context
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
