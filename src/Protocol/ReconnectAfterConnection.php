<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

final class ReconnectAfterConnection implements ConnectionInterface
{
    /**
     * @var ConnectionInterface
     */
    private $decoratedConnection;
    /**
     * @var \DateTimeImmutable
     */
    private $connectedAt;
    /**
     * @var \DateInterval
     */
    private $interval;

    /**
     * AppendCrlfConnection constructor.
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
        $this->decoratedConnection->connect();
        $this->connectedAt = new \DateTimeImmutable('now');
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
        $this->validateConnection();
        return $this->decoratedConnection->send($request);
    }

    /**
     * @return string
     */
    public function receive(): string
    {
        $this->validateConnection();
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

    /**
     *
     */
    private function validateConnection(): void
    {
        if ($this->connectedAt === null) {
            throw new \RuntimeException('Never connected at all');
        }

        if ($this->connectedAt->add($this->interval) < new \DateTimeImmutable()) {
            $this->disconnect();
            $this->connect();
        }
    }
}