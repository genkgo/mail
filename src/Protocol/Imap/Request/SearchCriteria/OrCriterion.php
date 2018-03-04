<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class OrCriterion implements CriterionInterface
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
        if ($query->count() !== 2) {
            throw new \InvalidArgumentException('OR criterium expects two expressions');
        }

        $this->query = $query;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            '(OR %s)',
            (string)$this->query
        );
    }
}
