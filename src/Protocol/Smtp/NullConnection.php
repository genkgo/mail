<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;

final class NullConnection implements ConnectionInterface
{
    private $receive;

    public function addListener(string $name, \Closure $callback): void
    {
    }

    public function connect(): void
    {
    }

    public function disconnect(): void
    {
    }

    public function send(string $request): int
    {
        $this->pushResponseToBuffer($request);

        return 0;
    }

    public function receive(): string
    {
        return $this->receive;
    }

    public function upgrade(int $type): void
    {
    }

    public function timeout(float $timeout): void
    {
    }

    public function getMetaData(array $keys = []): array
    {
        return [];
    }

    private function pushResponseToBuffer(string $request): void
    {
        $command = \explode(' ', \strtoupper(\trim($request)));
        switch ($command[0]) {
            case 'AUTH':
            case 'HELO':
            case 'EHLO':
            case 'MAIL':
            case 'RCPT':
            case 'RSET':
            case 'NOOP':
            case '.':
                $this->receive = '250 Ok';
                break;
            case 'DATA':
                $this->receive = '354 End data with <CR><LF>.<CR><LF>';
                break;
            case 'QUIT':
                $this->receive = '221 Bye';
                break;
            case 'VRFY':
                $this->receive = '252 null@null';
                break;
            default:
                $this->receive = '';
        }
    }
}
