<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

interface CriterionInterface
{
    /**
     * @return string
     */
    public function __toString(): string;
}
