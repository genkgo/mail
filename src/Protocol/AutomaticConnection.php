<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

final class AutomaticConnection implements ConnectionInterface
{
    /**
     * @var ConnectionInterface
     */
    private $decoratedConnection;

    /**
     * @var \DateTimeImmutable|null
     */
    private $connectedAt;

    /**
     * @var \DateInterval
     */
    private $interval;

    /**
     * @var bool
     */
    private $connecting = false;

    /**
     * @param ConnectionInterface $decoratedConnection
     * @param \DateInterval $interval
     */
    public function __construct(ConnectionInterface $decoratedConnection, \DateInterval $interval)
    {
        $this->decoratedConnection = $decoratedConnection;
        $this->interval = $interval;
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
        if ($this->connecting === false) {
            try {
                $this->connecting = true;
                $this->decoratedConnection->connect();
                $this->connectedAt = new \DateTimeImmutable('now');
            } finally {
                $this->connecting = false;
            }
        }
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        $this->decoratedConnection->disconnect();
        $this->connectedAt = null;
    }

    /**
     * @param string $request
     * @return int
     */
    public function send(string $request): int
    {
        $this->connectIfNotConnected();
        return $this->decoratedConnection->send($request);
    }

    /**
     * @return string
     */
    public function receive(): string
    {
        $this->connectIfNotConnected();
        return $this->decoratedConnection->receive();
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
    
    private function connectIfNotConnected(): void
    {
        if ($this->connectedAt === null) {
            $this->connect();
        }

        $now = new \DateTimeImmutable();
        if ($this->connecting === false && $this->connectedAt && $this->connectedAt->add($this->interval) < $now) {
            $this->disconnect();
            $this->connect();
        }
    }
}
