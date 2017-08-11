<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header\Dkim;

final class RelaxedCanonicalizeBody implements CanonicalizeBodyInterface
{

    /**
     * @param string $string
     * @return string
     */
    public function canonicalize(string $string): string
    {
        $length = strlen($string);
        $canon = '';
        $lastChar = null;
        $space = false;
        $line = '';
        $emptyCounter = 0;

        for ($i = 0; $i < $length; ++$i) {
            switch ($string[$i]) {
                case "\r":
                    $lastChar = "\r";
                    break;

                case "\n":
                    if ($lastChar === "\r") {
                        $space = false;

                        if ($line === '') {
                            ++$emptyCounter;
                        } else {
                            $line = '';
                            $canon .= "\r\n";
                        }
                    } else {
                        throw new \RuntimeException('This should never happen');
                    }

                    break;

                case ' ':
                case "\t":
                    $space = true;
                    break;

                default:
                    if ($emptyCounter > 0) {
                        $canon .= str_repeat("\r\n", $emptyCounter);
                        $emptyCounter = 0;
                    }

                    if ($space) {
                        $line .= ' ';
                        $canon .= ' ';
                        $space = false;
                    }

                    $line .= $string[$i];
                    $canon .= $string[$i];
                    break;
            }
        }

        if ($line === '') {
            $canon .= "\r\n";
        }

        return $canon;
    }
}