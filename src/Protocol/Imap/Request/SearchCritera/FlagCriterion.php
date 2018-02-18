<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Flag;

/**
 * Class FlagCriterium
 * @package Genkgo\Mail\Protocol\Imap\Request\SearchCriteria
 */
final class FlagCriterion implements CriterionInterface
{
    /**
     * @var
     */
    private $flag;

    /**
     * FlagCriterium constructor.
     * @param Flag $flag
     */
    public function __construct(Flag $flag)
    {
        if ($flag->isKeyword()) {
            throw new \InvalidArgumentException(
                'Use KeywordFlag criterion for keyword flag search'
            );
        }

        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->flag;
    }

    /**
     * @return FlagCriterion
     */
    public static function answered(): self
    {
        return new self(new Flag('ANSWERED'));
    }

    /**
     * @return FlagCriterion
     */
    public static function draft(): self
    {
        return new self(new Flag('DRAFT'));
    }

    /**
     * @return FlagCriterion
     */
    public static function deleted(): self
    {
        return new self(new Flag('DELETED'));
    }

    /**
     * @return FlagCriterion
     */
    public static function flagged(): self
    {
        return new self(new Flag('FLAGGED'));
    }

    /**
     * @return FlagCriterion
     */
    public static function new(): self
    {
        return new self(new Flag('NEW'));
    }

    /**
     * @return FlagCriterion
     */
    public static function old(): self
    {
        return new self(new Flag('OLD'));
    }

    /**
     * @return FlagCriterion
     */
    public static function recent(): self
    {
        return new self(new Flag('RECENT'));
    }

    /**
     * @return FlagCriterion
     */
    public static function seen(): self
    {
        return new self(new Flag('SEEN'));
    }

    /**
     * @return FlagCriterion
     */
    public static function unanswered(): self
    {
        return new self(new Flag('UNANSWERED'));
    }

    /**
     * @return FlagCriterion
     */
    public static function undraft(): self
    {
        return new self(new Flag('UNDRAFT'));
    }

    /**
     * @return FlagCriterion
     */
    public static function undeleted(): self
    {
        return new self(new Flag('UNDELETED'));
    }

    /**
     * @return FlagCriterion
     */
    public static function unflagged(): self
    {
        return new self(new Flag('UNFLAGGED'));
    }

    /**
     * @return FlagCriterion
     */
    public static function unseen(): self
    {
        return new self(new Flag('UNSEEN'));
    }
}