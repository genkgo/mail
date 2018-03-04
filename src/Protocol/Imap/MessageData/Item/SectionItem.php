<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\MessageData\ItemInterface;
use Genkgo\Mail\Protocol\Imap\MessageData\SectionList;

final class SectionItem implements ItemInterface
{
    /**
     * @var ItemInterface
     */
    private $nameItem;

    /**
     * @var SectionList
     */
    private $sectionList;

    /**
     * @param ItemInterface $nameItem
     * @param SectionList $sectionList
     */
    public function __construct(ItemInterface $nameItem, SectionList $sectionList)
    {
        $this->nameItem = $nameItem;
        $this->sectionList = $sectionList;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->nameItem->getName();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            '%s%s',
            $this->getName(),
            (string)$this->sectionList
        );
    }
}
