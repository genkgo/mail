<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class RenameCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class RenameCommand extends AbstractCommand
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
     * @var string
     */
    private $newName;

    /**
     * FetchCommand constructor.
     * @param Tag $tag
     * @param string $mailboxName
     * @param string $newName
     */
    public function __construct(Tag $tag, string $mailboxName, string $newName)
    {
        $this->tag = $tag;
        $this->mailboxName = $mailboxName;
        $this->newName = $newName;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'RENAME %s %s',
                $this->mailboxName,
                $this->newName
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