<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MessageDataItemList;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class FetchCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class FetchCommand extends AbstractCommand
{
    /**
     * @var SequenceSet
     */
    private $set;
    /**
     * @var MessageDataItemList
     */
    private $list;

    /**
     * FetchCommand constructor.
     * @param SequenceSet $set
     */
    public function __construct(SequenceSet $set, MessageDataItemList $list)
    {
        $this->set = $set;
        $this->list = $list;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'FETCH %s %s',
                (string)$this->set,
                (string)$this->list
            )
        );
    }
}