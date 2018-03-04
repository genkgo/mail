<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class NotCriterion implements CriterionInterface
{
    /**
     * @var Query
     */
    private $query;

    /**
     * OrCriterium constructor.
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        if ($query->count() === 0) {
            throw new \InvalidArgumentException('NOT criterium expects query with at least one expression');
        }

        $this->query = $query;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            '(NOT %s)',
            (string)$this->query
        );
    }
}
