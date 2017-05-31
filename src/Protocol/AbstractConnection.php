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
        $this->connect();

        $bytesWritten = fwrite($this->resource, $request);

        if ($bytesWritten === false) {
            throw new \RuntimeException(sprintf('Could not send command:'));
        }

        $this->verifyConnection();

        return $bytesWritten;
    }

    /**
     * @return string
     */
    final public function receive(): string
    {
        $this->connect();

        $response = fgets($this->resource, self::RECEIVE_BYTES);

        $this->verifyConnection();

        return $response;
    }

    /**
     *
     */
    private function verifyConnection()
    {
        $info = stream_get_meta_data($this->resource);
        if ($info['timed_out']) {
            throw new \RuntimeException('Connection has timed out');
        }
    }

    /**
     *
     */
    abstract protected function connect();
}