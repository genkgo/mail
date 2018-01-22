<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

/**
 * Class SequenceSet
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class SequenceSet
{

    /**
     * @var int
     */
    private $first;
    /**
     * @var int
     */
    private $last;

    /**
     * SequenceSet constructor.
     * @param int $first
     */
    public function __construct(int $first)
    {
        $this->first = $first;
    }

    /**
     * @param int $last
     * @return self
     */
    public function withLast(int $last): self
    {
        $clone = clone $this;
        $clone->last = $last;
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s:%s',
            $this->first,
            $this->last ? $this->last : '*'
        );
    }

    /**
     * @param int $number
     * @return SequenceSet
     */
    public static function single(int $number): self
    {
        $set = new self($number);
        $set->last = $number;
        return $set;
    }
}