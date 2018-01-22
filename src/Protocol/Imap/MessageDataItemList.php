<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

/**
 * Class MessageDataItemList
 * @package Genkgo\Mail\Protocol\Imap
 */
final class MessageDataItemList
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * @param string $name
     * @return MessageDataItemList
     */
    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->list[$name] = $name;
        return $clone;
    }

    /**
     * @param string $macro
     * @return MessageDataItemList
     */
    public function withMacro(string $macro): self
    {
        $clone = clone $this;
        $clone->list[$macro] = $macro;
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = implode(' ', $this->list);

        if (count($this->list) > 1) {
            return '(' . $string . ')';
        }

        return $string;
    }

    /**
     * @param string $dataItems
     * @return MessageDataItemList
     */
    public static function fromString(string $dataItems): self
    {
        return new self();
    }
}