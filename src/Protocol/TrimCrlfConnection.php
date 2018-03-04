<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

final class TrimCrlfConnection implements ConnectionInterface
{
    /**
     * @var ConnectionInterface
     */
    private $decoratedConnection;

    /**
     * @param ConnectionInterface $decoratedConnection
     */
    public function __construct(ConnectionInterface $decoratedConnection)
    {
        $this->decoratedConnection = $decoratedConnection;
    }

    /**
     * @param string $name
     * @param \Closure $callback
     */
    public function addListener(string $name, \Closure $callback): void
    {
        $this->decoratedConnection->addListener($name, $callback);
    }

    /**
     * @return void
     */
    public function connect(): void
    {
        $this->decoratedConnection->connect();
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        $this->decoratedConnection->disconnect();
    }

    /**
     * @param string $request
     * @return int
     */
    public function send(string $request): int
    {
        return $this->decoratedConnection->send($request);
    }

    /**
     * @return string
     */
    public function receive(): string
    {
        return \rtrim($this->decoratedConnection->receive(), "\r\n");
    }

    /**
     * @param int $type
     */
    public function upgrade(int $type): void
    {
        $this->decoratedConnection->upgrade($type);
    }

    /**
     * @param float $timeout
     */
    public function timeout(float $timeout): void
    {
        $this->decoratedConnection->timeout($timeout);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMetaData(array $keys = []): array
    {
        return $this->decoratedConnection->getMetaData($keys);
    }
}
