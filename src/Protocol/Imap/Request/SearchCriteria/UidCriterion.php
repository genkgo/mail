<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;

final class UidCriterion implements CriterionInterface
{
    /**
     * @var SequenceSet
     */
    private $set;

    /**
     * UidCriterium constructor.
     * @param SequenceSet $set
     */
    public function __construct(SequenceSet $set)
    {
        $this->set = $set;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'UID ' . (string)$this->set;
    }
}
