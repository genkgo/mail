<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Genkgo\Mail\Exception\CannotWriteToStreamException;
use Genkgo\Mail\Exception\ConnectionBrokenException;
use Genkgo\Mail\Exception\ConnectionTimeoutException;
use Genkgo\Mail\Exception\ConnectionClosedException;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractConnection implements ConnectionInterface
{
    private const RECEIVE_BYTES = 1024;

    /**
     * @var resource|null
     */
    protected $resource;

    /**
     * @var array<string, array<int, \Closure>>
     */
    private $listeners = [
        'connect' => []
    ];
    
    final public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @param string $name
     * @param \Closure $callback
     */
    final public function addListener(string $name, \Closure $callback): void
    {
        $this->listeners[$name][] = $callback;
    }

    /**
     * @param string $name
     */
    final protected function fireEvent(string $name): void
    {
        if (!isset($this->listeners[$name])) {
            return;
        }

        foreach ($this->listeners[$name] as $listener) {
            $listener();
        }
    }
    
    abstract public function connect(): void;
    
    final public function disconnect(): void
    {
        if ($this->resource !== null) {
            \fclose($this->resource);
            $this->resource = null;
        }
    }

    /**
     * @param float $timeout
     */
    final public function timeout(float $timeout): void
    {
        \stream_set_timeout($this->verifyConnection(), (int)$timeout);
    }

    /**
     * @param string $request
     * @return int
     * @throws CannotWriteToStreamException
     */
    final public function send(string $request): int
    {
        $bytesWritten = \fwrite($this->verifyConnection(), $request);

        if ($bytesWritten === false) {
            throw new CannotWriteToStreamException();
        }

        return $bytesWritten;
    }

    /**
     * @return string
     */
    final public function receive(): string
    {
        $response = \fgets($this->verifyConnection(), self::RECEIVE_BYTES);

        $this->verifyAlive();

        if ($response === false) {
            throw new ConnectionBrokenException('Cannot receive information');
        }

        return $response;
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    final public function getMetaData(array $keys = []): array
    {
        $resource = $this->verifyAlive();

        $metaData = \stream_get_meta_data($resource);

        $keys = \array_map('strtolower', $keys);

        return \array_filter(
            $metaData,
            function ($key) use ($keys) {
                return \in_array(\strtolower($key), $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return resource
     */
    private function verifyConnection()
    {
        if ($this->resource === null) {
            throw new \UnexpectedValueException('Cannot communicate when there is no connection');
        }

        return $this->resource;
    }

    /**
     * @return resource
     * @throws ConnectionClosedException
     * @throws ConnectionTimeoutException
     */
    private function verifyAlive()
    {
        $resource = $this->verifyConnection();
        $info = \stream_get_meta_data($resource);
        if ($info['timed_out']) {
            throw new ConnectionTimeoutException('Connection has timed out');
        }

        if ($info['eof']) {
            throw new ConnectionClosedException('Connection is gone');
        }

        return $resource;
    }
}
