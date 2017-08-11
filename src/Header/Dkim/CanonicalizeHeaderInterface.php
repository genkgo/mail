<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header\Dkim;

use Genkgo\Mail\HeaderInterface;

interface CanonicalizeHeaderInterface
{

    /**
     * @param HeaderInterface $header
     * @return string
     */
    public function canonicalize(HeaderInterface $header): string;

    /**
     * @return string
     */
    public static function getName(): string;
}