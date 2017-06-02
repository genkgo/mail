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
    public function execute(ConnectionInterface $connection)
    {
        while (!$this->stream->eof()) {
            $bytes = $this->stream->read(1000);
            $lines = explode("\r\n", $bytes);
            foreach ($lines as $line) {
                $line = rtrim($line, "\r");
                if (isset($line[0]) && $line[0] === '.') {
                    $line = '.' . $line;
                }

                $connection->send($line);
            }
        }

        $connection->send('.');
    }
}