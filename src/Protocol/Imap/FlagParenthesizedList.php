<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

final class FlagParenthesizedList
{
    /**
     * @var array
     */
    private $flags = [];

    /**
     * @param array $list
     */
    public function __construct(array $list = [])
    {
        $this->flags = $list;
    }

    /**
     * @param Flag $flag
     * @return FlagParenthesizedList
     */
    public function with(Flag $flag): self
    {
        $clone = clone $this;
        $clone->flags[(string)$flag] = $flag;
        return $clone;
    }

    /**
     * @param Flag $flag
     * @return FlagParenthesizedList
     */
    public function without(Flag $flag): self
    {
        $clone = clone $this;
        unset($clone->flags[(string)$flag]);
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (empty($this->flags)) {
            return '';
        }

        return \sprintf(
            '(%s)',
            \implode(' ', $this->flags)
        );
    }
}
