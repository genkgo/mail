<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class MatchContentCriterion implements CriterionInterface
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $name;

    /**
     * BodyCriterium constructor.
     * @param string $name
     * @param string $query
     */
    private function __construct(string $name, string $query)
    {
        if ($query === '' || \strlen($query) !== \strcspn($query, "\r\n")) {
            throw new \InvalidArgumentException('CR and LF are not allowed in quoted strings');
        }

        $this->name = $name;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            '%s "%s"',
            $this->name,
            \addslashes($this->query)
        );
    }

    /**
     * @param string $query
     * @return MatchContentCriterion
     */
    public static function body(string $query): self
    {
        return new self('BODY', $query);
    }

    /**
     * @param string $query
     * @return MatchContentCriterion
     */
    public static function text(string $query): self
    {
        return new self('TEXT', $query);
    }

    /**
     * @param string $query
     * @return MatchContentCriterion
     */
    public static function subject(string $query): self
    {
        return new self('SUBJECT', $query);
    }
}
