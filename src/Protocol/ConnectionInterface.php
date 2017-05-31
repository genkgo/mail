<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

interface ConnectionInterface
{
    /**
     * @param int $type
     * @return ConnectionInterface
     */
    public function upgrade(int $type): ConnectionInterface;

    /**
     * @param float $timeout
     */
    public function timeout(float $timeout): void;

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
     * @return void
     */
    public function disconnect(): void;

}