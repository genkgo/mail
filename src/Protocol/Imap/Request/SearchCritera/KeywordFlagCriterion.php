<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Flag;

/**
 * Class KeywordFlagCriterium
 * @package Genkgo\Mail\Protocol\Imap\Request\SearchCriteria
 */
final class KeywordFlagCriterion implements CriterionInterface
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var Flag
     */
    private $flag;

    /**
     * KeywordFlagCriterium constructor.
     * @param string $name
     * @param Flag $flag
     */
    private function __construct(string $name, Flag $flag)
    {
        $this->name = $name;
        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s %s',
            $this->name,
            (string)$this->flag
        );
    }

    /**
     * @param Flag $flag
     * @return KeywordFlagCriterion
     */
    public static function keyword(Flag $flag): self
    {
        return new self('KEYWORD', $flag);
    }

    /**
     * @param Flag $flag
     * @return KeywordFlagCriterion
     */
    public static function unkeyword(Flag $flag): self
    {
        return new self('UNKEYWORD', $flag);
    }
}