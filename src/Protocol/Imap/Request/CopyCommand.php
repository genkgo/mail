<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class CopyCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class CopyCommand extends AbstractCommand
{
    /**
     * @var SequenceSet
     */
    private $set;
    /**
     * @var Tag
     */
    private $tag;
    /**
     * @var string
     */
    private $mailbox;

    /**
     * CopyCommand constructor.
     * @param Tag $tag
     * @param SequenceSet $set
     * @param string $mailbox
     */
    public function __construct(Tag $tag, SequenceSet $set, string $mailbox)
    {
        $this->set = $set;
        $this->tag = $tag;
        $this->mailbox = $mailbox;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'COPY %s %s',
                (string)$this->set,
                $this->mailbox
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