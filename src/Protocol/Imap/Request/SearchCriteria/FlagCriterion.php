<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Flag;

final class FlagCriterion implements CriterionInterface
{
    /**
     * @var Flag
     */
    private $flag;

    /**
     * @var bool
     */
    private $negate = false;

    /**
     * @param Flag $flag
     */
    public function __construct(Flag $flag)
    {
        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $prefix = $this->negate ? 'UN' : '';

        if ($this->flag->isKeyword()) {
            return \sprintf(
                '%sKEYWORD %s',
                $prefix,
                (string)$this->flag
            );
        }

        return $prefix . \strtoupper(\substr((string)$this->flag, 1));
    }

    /**
     * @param Flag $flag
     * @return FlagCriterion
     */
    public static function negate(Flag $flag): self
    {
        $criterion = new self($flag);
        $criterion->negate = true;
        return $criterion;
    }
}
