<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header\Dkim;

use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\HeaderInterface;

final class SimpleCanonicalizeHeader implements CanonicalizeHeaderInterface
{
    /**
     * @param HeaderInterface $header
     * @return string
     */
    public function canonicalize(HeaderInterface $header): string
    {
        return (string)new HeaderLine($header);
    }
}