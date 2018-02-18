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
     *
     */
    private CONST RFC_3501_SECTION_FIXED = [
        'HEADER' => true,
        'TEXT' => true,
    ];

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
        foreach ($sections as $section) {
            $this->validateSection($section);
        }

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

        $sections = array_filter(explode(' ', $matches[1]));
        for ($i = 0; $i < count($sections); $i++) {
            if ($sections[$i] === 'HEADER.FIELDS' || $sections[$i] === 'HEADER.FIELDS.NOT') {
                if ($sections[$i + 1][0] !== '(') {
                    throw new \InvalidArgumentException(
                        'HEADER.FIELDS or HEADER.FIELDS.NOT requires header-list'
                    );
                }

                do {
                    $sections[$i] .= $sections[$i + 1];
                    unset($sections[$i + 1]);
                } while (isset($sections[$i + 1]) && substr($sections[$i + 1], -1) !== ')');
            }
        }

        $sectionList = new self($sections);

        if (empty($sectionList->sections)) {
            $sectionList->forceBrackets = true;
        }

        return $sectionList;
    }

    /**
     * @param string $section
     */
    private function validateSection(string $section): void
    {
        if ($section === '') {
            throw new \InvalidArgumentException('Empty section');
        }

        if (isset(self::RFC_3501_SECTION_FIXED[$section])) {
            return;
        }

        if (preg_match('/^HEADER.FIELDS(\.NOT)? \((.*?)\)$/', $section) === 1) {
            return;
        }

        throw new \InvalidArgumentException('Invalid section item ' . $section);
    }
}