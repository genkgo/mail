<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

final class Item
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var SectionList $sectionList
     */
    private $sections;
    /**
     * @var Partial
     */
    private $partial;

    /**
     * NameItem constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param SectionList $sectionList
     * @return Item
     */
    public function withSections(SectionList $sectionList)
    {
        $clone = clone $this;
        $clone->sections = $sectionList;
        return $clone;
    }

    /**
     * @param Partial $partial
     * @return Item
     */
    public function withPartial(Partial $partial): self
    {
        $clone = clone $this;
        $clone->partial = $partial;
        return $clone;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s%s%s',
            $this->name,
            $this->sections ? (string)$this->sections : '',
            $this->partial ? (string)$this->partial : ''
        );
    }
}