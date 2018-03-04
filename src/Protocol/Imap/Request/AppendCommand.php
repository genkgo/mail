<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class AppendCommand extends AbstractCommand
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
     * @var FlagParenthesizedList|null
     */
    private $flags;

    /**
     * @var int
     */
    private $size;

    /**
     * @var \DateTimeImmutable|null
     */
    private $internalDate;

    /**
     * @param Tag $tag
     * @param MailboxName $mailbox
     * @param int $size
     * @param FlagParenthesizedList|null $flags
     * @param \DateTimeImmutable|null $internalDate
     */
    public function __construct(
        Tag $tag,
        MailboxName $mailbox,
        int $size,
        FlagParenthesizedList $flags = null,
        \DateTimeImmutable $internalDate = null
    ) {
        $this->tag = $tag;
        $this->mailbox = $mailbox;
        $this->flags = $flags;
        $this->size = $size;
        $this->internalDate = $internalDate;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'APPEND %s %s%s{%s}',
                (string)$this->mailbox,
                $this->flags ? (string)$this->flags . ' ' : '',
                $this->internalDate ? '"'.$this->internalDate->format('d-D-Y H:i:sO') . '" ' : '',
                (string)$this->size
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
