<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\TagFactory;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Protocol\Imap\TagFactoryInterface;

/**
 * Class GeneratorTagFactory
 * @package Genkgo\Mail\Protocol\Imap\TagFactory
 */
final class GeneratorTagFactory implements TagFactoryInterface
{
    /**
     * @var \Generator
     */
    private $iterator;

    /**
     * GeneratorTagFactory constructor.
     */
    public function __construct()
    {
        $this->iterator = $this->newList();
    }

    /**
     * @return Tag
     */
    public function newTag(): Tag
    {
        $tag = $this->iterator->current();
        $this->iterator->next();
        return $tag;
    }

    /**
     * @return \Generator
     */
    private function newList(): \Generator
    {
        $i = 0;

        while (true) {
            yield Tag::fromNonce(++$i);
        }
    }
}