<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class StartTlsCommand extends AbstractCommand
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * StartTlsCommand constructor.
     * @param Tag $tag
     */
    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return StreamInterface
     */
    public function createStream(): StreamInterface
    {
        return new StringStream('STARTTLS');
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }
}
