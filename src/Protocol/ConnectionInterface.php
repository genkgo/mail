<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

interface ConnectionInterface
{
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
     */
    public function upgrade(int $type): void;

    /**
     * @param float $timeout
     */
    public function timeout(float $timeout): void;

}