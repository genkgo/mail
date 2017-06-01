<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

abstract class AbstractConnection implements ConnectionInterface
{
    /**
     *
     */
    private const RECEIVE_BYTES = 1024;
    /**
     * @var resource
     */
    protected $resource;

    /**
     *
     */
    final public function __destruct()
    {
        $this->disconnect();
    }

    /**
     *
     */
    abstract public function connect(): void;

    /**
     *
     */
    final public function disconnect(): void
    {
        fclose($this->resource);
        $this->resource = null;
    }

    /**
     * @param float $timeout
     */
    final public function timeout(float $timeout): void
    {
        stream_set_timeout($this->resource, $timeout);
    }

    /**
     * @param string $request
     * @return int
     */
    final public function send(string $request): int
    {
        $this->verifyConnection();

        $bytesWritten = fwrite($this->resource, $request);

        if ($bytesWritten === false) {
            throw new \RuntimeException(sprintf('Could not send command:'));
        }

        $this->verifyAlive();

        return $bytesWritten;
    }

    /**
     * @return string
     */
    final public function receive(): string
    {
        $this->verifyConnection();

        $response = fgets($this->resource, self::RECEIVE_BYTES);

        $this->verifyAlive();

        return $response;
    }

    /**
     *
     */
    private function verifyConnection()
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Cannot send/receive data while not connected');
        }
    }

    /**
     *
     */
    private function verifyAlive()
    {
        $info = stream_get_meta_data($this->resource);
        if ($info['timed_out']) {
            throw new \RuntimeException('Connection has timed out');
        }
    }
}