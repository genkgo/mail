<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\MessageStream;
use Genkgo\Mail\StreamInterface;

final class AppendDataRequest implements RequestInterface
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @param Tag $tag
     * @param MessageInterface $message
     */
    public function __construct(Tag $tag, MessageInterface $message)
    {
        $this->tag = $tag;
        $this->message = $message;
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return new MessageStream($this->message);
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }
}
