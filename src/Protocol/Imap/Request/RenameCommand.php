<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class RenameCommand extends AbstractCommand
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
     * @var MailboxName
     */
    private $newName;

    /**
     * @param Tag $tag
     * @param MailboxName $mailbox
     * @param MailboxName $newName
     */
    public function __construct(Tag $tag, MailboxName $mailbox, MailboxName $newName)
    {
        $this->tag = $tag;
        $this->mailbox = $mailbox;
        $this->newName = $newName;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'RENAME %s %s',
                (string)$this->mailbox,
                (string)$this->newName
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
