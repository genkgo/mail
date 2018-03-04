<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\FlagsItem;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class StoreCommand extends AbstractCommand
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var SequenceSet
     */
    private $sequenceSet;

    /**
     * @var FlagsItem
     */
    private $flagsItem;

    /**
     * @param Tag $tag
     * @param SequenceSet $sequenceSet
     * @param FlagsItem $flagsItem
     */
    public function __construct(
        Tag $tag,
        SequenceSet $sequenceSet,
        FlagsItem $flagsItem
    ) {
        $this->tag = $tag;
        $this->flagsItem = $flagsItem;
        $this->sequenceSet = $sequenceSet;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'STORE %s %s',
                (string)$this->sequenceSet,
                (string)$this->flagsItem
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
