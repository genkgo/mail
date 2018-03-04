<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\StreamInterface;

interface CanonicalizeBodyInterface
{
    /**
     * @param StreamInterface $body
     * @return string
     */
    public function canonicalize(StreamInterface $body): string;

    /**
     * @return string
     */
    public function name(): string;
}
