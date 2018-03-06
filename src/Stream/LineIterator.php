<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class LineIterator extends \IteratorIterator
{
    /**
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        parent::__construct($this->newIterator($stream));
    }

    /**
     * @param StreamInterface $stream
     * @return \Generator
     */
    private function newIterator(StreamInterface $stream): \Generator
    {
        $stream->rewind();

        $bytes = '';
        while (!$stream->eof()) {
            $bytes .= $stream->read(1000);

            $index = 0;
            while (isset($bytes[$index])) {
                if ($bytes[$index] === "\r" && isset($bytes[$index + 1]) && $bytes[$index + 1] === "\n") {
                    $line = \substr($bytes, 0, $index);
                    $bytes = \substr($bytes, $index + 2);
                    $index = -1;

                    yield $line;
                }

                $index++;
            }
        }

        yield $bytes;
    }
}
