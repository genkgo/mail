<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\StreamInterface;

interface PartInterface
{
    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary;

    /**
     * @param Boundary $boundary
     * @return PartInterface
     */
    public function withBoundary(Boundary $boundary): PartInterface;

    /**
     * @return iterable
     */
    public function getHeaders(): iterable;

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool;

    /**
     * @param string $name
     * @return HeaderInterface
     */
    public function getHeader(string $name): HeaderInterface;

    /**
     * @param HeaderInterface $header
     * @return PartInterface
     */
    public function withHeader(HeaderInterface $header): PartInterface;

    /**
     * @param string $name
     * @return PartInterface
     */
    public function withoutHeader(string $name): PartInterface;

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface ;

    /**
     * @param StreamInterface $body
     * @return PartInterface
     */
    public function withBody(StreamInterface $body): PartInterface;

    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withPart(PartInterface $part): PartInterface;

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface;

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface;

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable;

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface;
}