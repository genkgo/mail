<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header\Dkim;

final class SimpleCanonicalizeBody implements CanonicalizeBodyInterface
{
    /**
     * @param string $string
     * @return string
     */
    public function canonicalize(string $string): string
    {
        if (substr($string, -2, 2) === "\r\n") {
            return $string . "\r\n";
        }

        return $string;
    }
}