<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

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
     * FetchCommand constructor.
     * @param Tag $tag
     * @param string $mailboxName
     * @param ParenthesizedList $flags
     * @param int $size
     */
    public function __construct(Tag $tag, string $mailboxName, int $size, ParenthesizedList $flags)
    {
        $this->tag = $tag;
        $this->mailboxName = $mailboxName;
        $this->flags = $flags;
        $this->size = $size;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'APPEND %s %s {%s}',
                $this->mailboxName,
                (string)$this->flags,
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