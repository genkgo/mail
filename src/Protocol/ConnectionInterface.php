<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Genkgo\Mail\Exception\SecureConnectionUpgradeException;

interface ConnectionInterface
{
    /**
     * @param string $name
     * @param \Closure $callback
     */
    public function addListener(string $name, \Closure $callback): void;

    /**
     * @return void
     */
    public function connect(): void;

    /**
     * @return void
     */
    public function disconnect(): void;

    /**
     * @param string $request
     * @return int
     */
    public function send(string $request): int;

    /**
     * @return string
     */
    public function receive(): string;

    /**
     * @param int $type
     * @throws SecureConnectionUpgradeException
     */
    public function upgrade(int $type): void;

    /**
     * @param float $timeout
     */
    public function timeout(float $timeout): void;

    /**
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public function getMetaData(array $keys = []): array;
}
