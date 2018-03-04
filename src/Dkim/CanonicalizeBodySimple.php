<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\StreamInterface;

final class CanonicalizeBodySimple implements CanonicalizeBodyInterface
{
    /**
     * @param StreamInterface $body
     * @return string
     */
    public function canonicalize(StreamInterface $body): string
    {
        return \rtrim((string)$body, "\r\n") . "\r\n";
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'simple';
    }
}
