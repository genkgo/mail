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

    /**
     * @param int $first
     * @param int $last
     * @return SequenceSet
     */
    public static function sequence(int $first, int $last): self
    {
        $set = new self($first);
        $set->last = $last;
        return $set;
    }
}