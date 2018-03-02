<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

/**
 * Interface CriterionInterface
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
interface CriterionInterface
{

    /**
     * @return string
     */
    public function __toString(): string;

}