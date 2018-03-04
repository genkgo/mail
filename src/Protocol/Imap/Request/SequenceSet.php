<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

final class SequenceSet
{
    /**
     * @var array
     */
    private $set = [];
    
    private function __construct()
    {
        // this constructor is protected in order to force
        // a set to have at least one item
    }

    /**
     * @param int $number
     * @return SequenceSet
     */
    public function withSingle(int $number): self
    {
        $set = clone $this;
        $set->set[] = $number;
        return $set;
    }

    /**
     * @param int $first
     * @param int $last
     * @return SequenceSet
     */
    public function withRange(int $first, int $last): self
    {
        $set = clone $this;
        $set->set[] = (string)$first.':'.(string)$last;
        return $set;
    }

    /**
     * @param int $first
     * @return SequenceSet
     */
    public function withInfiniteRange(int $first): self
    {
        $set = clone $this;
        $set->set[] = (string)$first.':*';
        return $set;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \implode(',', $this->set);
    }

    /**
     * @return SequenceSet
     */
    public static function all(): self
    {
        $set = new self();
        $set->set = ['*'];
        return $set;
    }

    /**
     * @param int $number
     * @return SequenceSet
     */
    public static function single(int $number): self
    {
        $set = new self();
        $set->set = [(string)$number];
        return $set;
    }

    /**
     * @param int $first
     * @param int $last
     * @return SequenceSet
     */
    public static function range(int $first, int $last): self
    {
        $set = new self();
        $set->set = [(string)$first.':'.(string)$last];
        return $set;
    }

    /**
     * @param int $first
     * @return SequenceSet
     */
    public static function infiniteRange(int $first): self
    {
        $set = new self();
        $set->set = [(string)$first.':*'];
        return $set;
    }
}
