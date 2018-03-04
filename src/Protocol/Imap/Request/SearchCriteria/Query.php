<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class Query implements \Countable
{
    /**
     * @var array
     */
    private $criteria = [];

    /**
     * @param array $criteria
     */
    public function __construct(array $criteria = [])
    {
        $this->criteria = $criteria;
    }

    /**
     * @param CriterionInterface $criterion
     * @return Query
     */
    public function with(CriterionInterface $criterion): self
    {
        $clone = clone $this;
        $clone->criteria[] = $criterion;
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \trim(\implode(' ', $this->criteria));
    }

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->criteria);
    }
}
