<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

interface MultiPartInterface extends PartInterface
{
    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary;

    /**
     * @param PartInterface $part
     * @return MultiPartInterface
     */
    public function withPart(PartInterface $part): MultiPartInterface;

    /**
     * @param iterable|MultiPartInterface[] $parts
     * @return MultiPartInterface
     */
    public function withParts(iterable $parts): MultiPartInterface;

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable;
}
