<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;
use Genkgo\Mail\StreamInterface;

final class DataRequest implements RequestInterface
{
    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * DataRequest constructor.
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
        $bytes = '';

        $this->stream->rewind();
        while (!$this->stream->eof()) {
            $bytes .= $this->stream->read(1000);

            $index = 0;
            while (isset($bytes[$index])) {
                if ($bytes[$index] === "\r" && isset($bytes[$index+1]) && $bytes[$index+1] === "\n") {
                    $line = \substr($bytes, 0, $index);
                    $bytes = \substr($bytes, $index + 2);
                    $index = -1;

                    $this->sendLine($connection, $line);
                }

                $index++;
            }
        }

        $this->sendLine($connection, $bytes);

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
