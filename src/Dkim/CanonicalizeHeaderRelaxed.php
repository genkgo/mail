<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\HeaderInterface;

final class CanonicalizeHeaderRelaxed implements CanonicalizeHeaderInterface
{
    /**
     * @param HeaderInterface $header
     * @return string
     */
    public function canonicalize(HeaderInterface $header): string
    {
        $name = \strtolower(\trim((string)$header->getName()));
        $value = \str_replace("\r\n", '', (string)$header->getValue());
        $value = \preg_replace("/[ \t][ \t]+/", ' ', $value);
        return $name . ':' . \trim($value);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'relaxed';
    }
}
