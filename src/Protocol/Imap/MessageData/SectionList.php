<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

/**
 * Class SectionList
 * @package Genkgo\Mail\Protocol\Imap\MessageData
 */
final class SectionList
{
    /**
     * @var array
     */
    private $sections = [];

    /**
     * @var bool
     */
    private $forceBrackets = false;

    /**
     * SectionList constructor.
     * @param array $sections
     */
    public function __construct(array $sections = [])
    {
        $this->sections = $sections;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->forceBrackets === false && empty($this->sections)) {
            return '';
        }

        return sprintf(
            '[%s]',
            implode(' ', $this->sections)
        );
    }

    /**
     * @return SectionList
     */
    public static function newEmpty(): self
    {
        $list = new self();
        $list->forceBrackets = true;
        return $list;
    }

    /**
     * @param string $sections
     * @return SectionList
     */
    public static function fromString(string $sections): self
    {
        $result = preg_match('/^\[(.*?)\]$/', $sections, $matches);
        if ($result !== 1) {
            throw new \InvalidArgumentException('No section list');
        }

        $sectionList = new self(array_filter(explode(' ', $matches[1])));

        if (empty($sectionList->sections)) {
            $sectionList->forceBrackets = true;
        }

        return $sectionList;
    }

}