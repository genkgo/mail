<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\CommandResponseCanBeParsedInterface;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Response\Command\ParsedFetchCommandResponse;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class FetchCommand extends AbstractCommand implements CommandResponseCanBeParsedInterface
{
    /**
     * @var SequenceSet
     */
    private $set;

    /**
     * @var ItemList
     */
    private $list;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @param Tag $tag
     * @param SequenceSet $set
     * @param ItemList $list
     */
    public function __construct(Tag $tag, SequenceSet $set, ItemList $list)
    {
        $this->set = $set;
        $this->list = $list;
        $this->tag = $tag;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'FETCH %s %s',
                (string)$this->set,
                (string)$this->list
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

    /**
     * @param \Iterator $lineIterator
     * @return ResponseInterface
     */
    public function createParsedResponse(\Iterator $lineIterator): ResponseInterface
    {
        return new ParsedFetchCommandResponse($lineIterator);
    }
}
