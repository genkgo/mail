<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

final class NameSectionItem implements ItemInterface
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $sections;

    /**
     * NameSectionItem constructor.
     * @param string $name
     * @param array $sections
     */
    public function __construct(string $name, array $sections = [])
    {
        $this->name = $name;
        $this->sections = $sections;
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
            '%s[%s]',
            $this->name,
            implode(
                ' ',
                $this->sections
            )
        );
    }

    public static function fromString(string $nameSectionItem)
    {
        $result = preg_match('/^([A-Z]+)\[(.*?)\]$/', $nameSectionItem, $matches);
        if ($result !== 1) {
            throw new \InvalidArgumentException('Not a name section item');
        }

        return new self($matches[1], explode(' ', $matches[2]));
    }
}