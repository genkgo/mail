<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\HeaderInterface;

final class CanonicalizeHeaderSimple implements CanonicalizeHeaderInterface
{
    /**
     * @param HeaderInterface $header
     * @return string
     */
    public function canonicalize(HeaderInterface $header): string
    {
        return (string)new HeaderLine($header);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'simple';
    }
}
