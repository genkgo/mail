<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class UnsubscribeCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class UnsubscribeCommand extends AbstractCommand
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
     * UnsubscribeCommand constructor.
     * @param Tag $tag
     * @param string $mailboxName
     */
    public function __construct(Tag $tag, string $mailboxName)
    {
        $this->tag = $tag;
        $this->mailboxName = $mailboxName;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'UNSUBSCRIBE %s',
                $this->mailboxName
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