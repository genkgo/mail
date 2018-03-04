<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class AllCriterion implements CriterionInterface
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'ALL';
    }
}
