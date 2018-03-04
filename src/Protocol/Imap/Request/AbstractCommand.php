<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

abstract class AbstractCommand implements RequestInterface
{
    /**
     * @return StreamInterface
     */
    abstract protected function createStream(): StreamInterface;

    /**
     * @return StreamInterface
     */
    final public function toStream(): StreamInterface
    {
        return new ConcatenatedStream([
            new StringStream((string)$this->getTag() . ' '),
            $this->createStream()
        ]);
    }
}
