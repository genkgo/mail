<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class SelectCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class SelectCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var Tag
     */
    private $tag;

    /**
     * SelectCommand constructor.
     * @param Tag $tag
     * @param string $name
     */
    public function __construct(Tag $tag, string $name)
    {
        $this->name = $name;
        $this->tag = $tag;
    }

    /**
     * @return StreamInterface
     */
    public function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'SELECT %s',
                $this->name
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