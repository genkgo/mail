<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

final class Message
{
    /**
     * @var array
     */
    private $parts = [];

    /**
     * @param PartInterface $part
     * @return Message
     */
    public function withPart(PartInterface $part): Message
    {
        $clone = clone $this;
        $clone->parts[] = $part;
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return Message
     */
    public function withoutPart(PartInterface $part): Message
    {
        $clone = clone $this;
        $key = array_search($part, $clone->parts, true);
        unset($clone->parts[$key]);
        return $clone;
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return Message
     */
    public function withParts(iterable $parts): Message
    {
        $clone = clone $this;

        foreach ($parts as $part) {
            $clone->parts[] = $part;
        }

        return $clone;
    }

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable
    {
        return $this->parts;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(
            "\r\n",
            array_merge(
                ['This is a multipart message in MIME format.'],
                $this->parts
            )
        );
    }
}