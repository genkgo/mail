<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\TagFactory;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Protocol\Imap\TagFactoryInterface;

final class GeneratorTagFactory implements TagFactoryInterface
{
    /**
     * @var \Generator|Tag[]
     */
    private $iterator;
    
    public function __construct()
    {
        $this->iterator = $this->newList();
    }

    /**
     * @return Tag
     */
    public function newTag(): Tag
    {
        /** @var Tag $tag */
        $tag = $this->iterator->current();
        $this->iterator->next();
        return $tag;
    }

    /**
     * @return \Generator|Tag[]
     * @codeCoverageIgnore
     */
    private function newList(): \Generator
    {
        $i = 0;

        /** @phpstan-ignore-next-line */
        while (true) {
            yield Tag::fromNonce(++$i);
        }
    }
}
