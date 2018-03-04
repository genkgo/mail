<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class SelectCommand extends AbstractCommand
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var MailboxName
     */
    private $mailbox;

    /**
     * @param Tag $tag
     * @param MailboxName $mailbox
     */
    public function __construct(Tag $tag, MailboxName $mailbox)
    {
        $this->tag = $tag;
        $this->mailbox = $mailbox;
    }

    /**
     * @return StreamInterface
     */
    public function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'SELECT %s',
                (string)$this->mailbox
            )
        );
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }
}
