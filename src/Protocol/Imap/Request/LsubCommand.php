<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxWildcard;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class LsubCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class LsubCommand extends AbstractCommand
{
    /**
     * @var Tag
     */
    private $tag;
    /**
     * @var MailboxWildcard
     */
    private $mailbox;
    /**
     * @var MailboxWildcard
     */
    private $referenceName;

    /**
     * LsubCommand constructor.
     * @param Tag $tag
     * @param MailboxWildcard $referenceName
     * @param MailboxWildcard $mailbox
     */
    public function __construct(Tag $tag, MailboxWildcard $referenceName, MailboxWildcard $mailbox)
    {
        $this->tag = $tag;
        $this->mailbox = $mailbox;
        $this->referenceName = $referenceName;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'LSUB %s %s',
                (string)$this->referenceName,
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
