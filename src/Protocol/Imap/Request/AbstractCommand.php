<?php

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class AbstractCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
abstract class AbstractCommand implements RequestInterface
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @return StreamInterface
     */
    abstract protected function createStream(): StreamInterface;

    /**
     * @return Tag
     */
    final public function getTag(): Tag
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     * @return RequestInterface
     */
    final public function withTag(Tag $tag): RequestInterface
    {
        $clone = clone $this;
        $clone->tag = $tag;
        return $clone;
    }

    /**
     * @return StreamInterface
     */
    final public function toStream(): StreamInterface
    {
        return new ConcatenatedStream([
            new StringStream($this->tag . ' '),
            $this->createStream()
        ]);
    }

}