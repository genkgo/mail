<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Psr\Log\LoggerInterface;

final class DataLogConnection implements ConnectionInterface
{
    /**
     * @var ConnectionInterface
     */
    private $decoratedConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConnectionInterface $decoratedConnection
     * @param LoggerInterface $logger
     */
    public function __construct(ConnectionInterface $decoratedConnection, LoggerInterface $logger)
    {
        $this->decoratedConnection = $decoratedConnection;
        $this->logger = $logger;
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
        $this->logger->info('-> ' . $request);
        return $this->decoratedConnection->send($request);
    }

    /**
     * @return string
     */
    public function receive(): string
    {
        $data = $this->decoratedConnection->receive();
        $this->logger->info('<- ' . $data);
        return $data;
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
