<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\MessageData\ItemInterface;
use Genkgo\Mail\Protocol\Imap\MessageData\Partial;

final class PartialItem implements ItemInterface
{
    /**
     * @var ItemInterface
     */
    private $decoratedItem;

    /**
     * @var Partial
     */
    private $partial;

    /**
     * @param ItemInterface $decoratedItem
     * @param Partial $partial
     * @internal param SectionList $sectionList
     */
    public function __construct(ItemInterface $decoratedItem, Partial $partial)
    {
        $this->decoratedItem = $decoratedItem;
        $this->partial = $partial;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->decoratedItem->getName();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->decoratedItem.(string)$this->partial;
    }
}
