<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\ParenthesizedList;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class AppendCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class AppendCommand extends AbstractCommand
{
    /**
     * @var Tag
     */
    private $tag;
    /**
     * @var string
     */
    private $mailboxName;
    /**
     * @var ParenthesizedList
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
     * AppendCommand constructor.
     * @param Tag $tag
     * @param string $mailboxName
     * @param int $size
     * @param FlagParenthesizedList $flags
     * @param \DateTimeImmutable|null $internalDate
     */
    public function __construct(
        Tag $tag,
        string $mailboxName,
        int $size,
        FlagParenthesizedList $flags = null,
        \DateTimeImmutable $internalDate = null
    )
    {
        $this->tag = $tag;
        $this->mailboxName = $mailboxName;
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
            sprintf(
                'APPEND %s %s%s{%s}',
                $this->mailboxName,
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