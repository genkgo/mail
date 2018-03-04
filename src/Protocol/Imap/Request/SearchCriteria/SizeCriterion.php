<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class SizeCriterion implements CriterionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string $name
     * @param int $size
     */
    private function __construct(string $name, int $size)
    {
        $this->name = $name;
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf('%s %s', $this->name, $this->size);
    }

    /**
     * @param int $size
     * @return SizeCriterion
     */
    public static function smaller(int $size): self
    {
        return new self('SMALLER', $size);
    }

    /**
     * @param int $size
     * @return SizeCriterion
     */
    public static function larger(int $size): self
    {
        return new self('LARGER', $size);
    }
}
