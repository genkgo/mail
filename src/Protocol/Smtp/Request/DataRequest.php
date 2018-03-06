<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;
use Genkgo\Mail\Stream\LineIterator;
use Genkgo\Mail\StreamInterface;

final class DataRequest implements RequestInterface
{
    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function execute(ConnectionInterface $connection): void
    {
        foreach (new LineIterator($this->stream) as $line) {
            $this->sendLine($connection, $line);
        }

        $connection->send('.');
    }

    /**
     * @param ConnectionInterface $connection
     * @param string $line
     */
    private function sendLine(ConnectionInterface $connection, string $line): void
    {
        $line = \rtrim($line, "\r");

        if (isset($line[0]) && $line[0] === '.') {
            $line = '.' . $line;
        }

        $connection->send($line);
    }
}
