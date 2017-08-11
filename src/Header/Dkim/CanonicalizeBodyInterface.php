<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header\Dkim;

interface CanonicalizeBodyInterface
{

    /**
     * @param string $string
     * @return string
     */
    public function canonicalize(string $string): string;

    /**
     * @return string
     */
    public static function getName(): string;

}