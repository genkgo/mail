<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

final class ParenthesizedList
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * ParenthesizedList constructor.
     * @param array $list
     */
    public function __construct(array $list = [])
    {
        $this->list = $list;
    }

    /**
     * @param string $name
     * @return ParenthesizedList
     */
    public function with(string $name): self
    {
        $clone = clone $this;
        $clone->list[$name] = $name;
        return $clone;
    }

    /**
     * @param string $name
     * @return ParenthesizedList
     */
    public function without(string $name): self
    {
        $clone = clone $this;
        unset($clone->list[$name]);
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (empty($this->list)) {
            return '';
        }

        return \sprintf(
            '(%s)',
            \implode(' ', $this->list)
        );
    }
}
