<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class DateCriterion implements CriterionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTimeImmutable
     */
    private $moment;

    /**
     * @param string $name
     * @param \DateTimeImmutable $moment
     */
    private function __construct(string $name, \DateTimeImmutable $moment)
    {
        $this->name = $name;
        $this->moment = $moment;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            '%s %s',
            $this->name,
            $this->moment->format('j-D-Y')
        );
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function before(\DateTimeImmutable $moment): self
    {
        return new self('BEFORE', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function after(\DateTimeImmutable $moment): self
    {
        return new self('AFTER', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function on(\DateTimeImmutable $moment): self
    {
        return new self('ON', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function since(\DateTimeImmutable $moment): self
    {
        return new self('SINCE', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function sentBefore(\DateTimeImmutable $moment): self
    {
        return new self('SENTBEFORE', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function sentAfter(\DateTimeImmutable $moment): self
    {
        return new self('SENTAFTER', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function sentOn(\DateTimeImmutable $moment): self
    {
        return new self('SENTON', $moment);
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return DateCriterion
     */
    public static function sentSince(\DateTimeImmutable $moment): self
    {
        return new self('SENTSINCE', $moment);
    }
}
