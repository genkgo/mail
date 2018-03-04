<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

final class SectionList
{
    private const RFC_3501_SECTION_FIXED = [
        'HEADER' => true,
        'TEXT' => true,
    ];

    /**
     * @var array
     */
    private $sections = [];

    /**
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
        return \sprintf(
            '[%s]',
            \implode(' ', $this->sections)
        );
    }

    /**
     * @param string $sections
     * @return SectionList
     */
    public static function fromString(string $sections): self
    {
        $result = \preg_match('/^\[(.*?)\]$/', $sections, $matches);
        if ($result !== 1) {
            throw new \InvalidArgumentException('No section list');
        }

        $sections = \array_filter(\explode(' ', $matches[1]));
        for ($i = 0; $i < \count($sections); $i++) {
            if ($sections[$i] === 'HEADER.FIELDS' || $sections[$i] === 'HEADER.FIELDS.NOT') {
                if (!isset($sections[$i + 1][0]) || $sections[$i + 1][0] !== '(') {
                    throw new \InvalidArgumentException(
                        'HEADER.FIELDS or HEADER.FIELDS.NOT requires header-list'
                    );
                }

                $appendIndex = $i;
                $i++;
                do {
                    $sections[$appendIndex] .= ' ' . $sections[$i];
                    unset($sections[$i]);
                    $i++;

                    if (\substr($sections[$appendIndex], -1) === ')') {
                        break;
                    }
                } while (isset($sections[$i]));
            }
        }

        return new self($sections);
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

        if (\preg_match('/^HEADER.FIELDS(\.NOT)? \((.*?)\)$/', $section) === 1) {
            return;
        }

        throw new \InvalidArgumentException('Invalid section item ' . $section);
    }
}
