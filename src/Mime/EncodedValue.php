<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

/**
 * Class EncodedValue
 * @package Genkgo\Email\Mime
 */
final class EncodedValue
{
    /**
     *
     */
    private const KEYS = [
        "\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07",
        "\x08","\x09","\x0A","\x0B","\x0C","\x0D","\x0E","\x0F",
        "\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17",
        "\x18","\x19","\x1A","\x1B","\x1C","\x1D","\x1E","\x1F",
        "\x7F","\x80","\x81","\x82","\x83","\x84","\x85","\x86",
        "\x87","\x88","\x89","\x8A","\x8B","\x8C","\x8D","\x8E",
        "\x8F","\x90","\x91","\x92","\x93","\x94","\x95","\x96",
        "\x97","\x98","\x99","\x9A","\x9B","\x9C","\x9D","\x9E",
        "\x9F","\xA0","\xA1","\xA2","\xA3","\xA4","\xA5","\xA6",
        "\xA7","\xA8","\xA9","\xAA","\xAB","\xAC","\xAD","\xAE",
        "\xAF","\xB0","\xB1","\xB2","\xB3","\xB4","\xB5","\xB6",
        "\xB7","\xB8","\xB9","\xBA","\xBB","\xBC","\xBD","\xBE",
        "\xBF","\xC0","\xC1","\xC2","\xC3","\xC4","\xC5","\xC6",
        "\xC7","\xC8","\xC9","\xCA","\xCB","\xCC","\xCD","\xCE",
        "\xCF","\xD0","\xD1","\xD2","\xD3","\xD4","\xD5","\xD6",
        "\xD7","\xD8","\xD9","\xDA","\xDB","\xDC","\xDD","\xDE",
        "\xDF","\xE0","\xE1","\xE2","\xE3","\xE4","\xE5","\xE6",
        "\xE7","\xE8","\xE9","\xEA","\xEB","\xEC","\xED","\xEE",
        "\xEF","\xF0","\xF1","\xF2","\xF3","\xF4","\xF5","\xF6",
        "\xF7","\xF8","\xF9","\xFA","\xFB","\xFC","\xFD","\xFE",
        "\xFF"
    ];

    /**
     *
     */
    private const REPLACE_VALUES = [
        "=00","=01","=02","=03","=04","=05","=06","=07",
        "=08","=09","=0A","=0B","=0C","=0D","=0E","=0F",
        "=10","=11","=12","=13","=14","=15","=16","=17",
        "=18","=19","=1A","=1B","=1C","=1D","=1E","=1F",
        "=7F","=80","=81","=82","=83","=84","=85","=86",
        "=87","=88","=89","=8A","=8B","=8C","=8D","=8E",
        "=8F","=90","=91","=92","=93","=94","=95","=96",
        "=97","=98","=99","=9A","=9B","=9C","=9D","=9E",
        "=9F","=A0","=A1","=A2","=A3","=A4","=A5","=A6",
        "=A7","=A8","=A9","=AA","=AB","=AC","=AD","=AE",
        "=AF","=B0","=B1","=B2","=B3","=B4","=B5","=B6",
        "=B7","=B8","=B9","=BA","=BB","=BC","=BD","=BE",
        "=BF","=C0","=C1","=C2","=C3","=C4","=C5","=C6",
        "=C7","=C8","=C9","=CA","=CB","=CC","=CD","=CE",
        "=CF","=D0","=D1","=D2","=D3","=D4","=D5","=D6",
        "=D7","=D8","=D9","=DA","=DB","=DC","=DD","=DE",
        "=DF","=E0","=E1","=E2","=E3","=E4","=E5","=E6",
        "=E7","=E8","=E9","=EA","=EB","=EC","=ED","=EE",
        "=EF","=F0","=F1","=F2","=F3","=F4","=F5","=F6",
        "=F7","=F8","=F9","=FA","=FB","=FC","=FD","=FE",
        "=FF"
    ];

    /**
     * @var string
     */
    private $value;
    /**
     * @var int
     */
    private $lineLength;
    /**
     * @var string
     */
    private $lineEnd;

    /**
     * EncodedValue constructor.
     * @param string $value
     * @param int $lineLength
     * @param string $lineEnd
     */
    public function __construct(string $value, int $lineLength, string $lineEnd)
    {
        $this->value = $value;
        $this->lineLength = $lineLength;
        $this->lineEnd = $lineEnd;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->encode('UTF-8');
    }

    /**
     * @param string $charset
     * @return string
     */
    private function encode(string $charset): string
    {
        // Reduce line-length by the length of the required delimiter, charsets and encoding
        $prefix = sprintf('=?%s?Q?', $charset);
        $lineLength = $this->lineLength - strlen($prefix) - 3;
        $value = $this->encodeQuotedPrintable($this->value);
        // Mail-Header required chars have to be encoded also:
        $value = str_replace(['?', ' ', '_'], ['=3F', '=20', '=5F'], $value);
        // initialize first line, we need it anyways
        $lines = [0 => ''];
        // Split encoded text into separate lines
        $tmp = '';
        while (strlen($value) > 0) {
            $currentLine = max(count($lines) - 1, 0);
            $token = $this->getNextQuotedPrintableToken($value);
            $substr = substr($value, strlen($token));
            $value = (false === $substr) ? '' : $substr;
            $tmp .= $token;
            if ($token === '=20') {
                // only if we have a single char token or space, we can append the
                // tempstring it to the current line or start a new line if necessary.
                $lineLimitReached = (strlen($lines[$currentLine] . $tmp) > $lineLength);
                $noCurrentLine = ($lines[$currentLine] === '');
                if ($noCurrentLine && $lineLimitReached) {
                    $lines[$currentLine] = $tmp;
                    $lines[$currentLine + 1] = '';
                } elseif ($lineLimitReached) {
                    $lines[$currentLine + 1] = $tmp;
                } else {
                    $lines[$currentLine] .= $tmp;
                }
                $tmp = '';
            }
            // don't forget to append the rest to the last line
            if (strlen($value) === 0) {
                $lines[$currentLine] .= $tmp;
            }
        }

        // assemble the lines together by pre- and appending delimiters, charset, encoding.
        for ($i = 0, $count = count($lines); $i < $count; $i++) {
            $lines[$i] = $prefix . $lines[$i] . "?=";
        }

        $value = trim(implode($this->lineEnd, $lines));
        return $value;
    }

    /**
     * @param  string $string
     * @return string
     */
    private function getNextQuotedPrintableToken(string $string): string
    {
        if (substr($string, 0, 1) === "=") {
            $token = substr($string, 0, 3);
        } else {
            $token = substr($string, 0, 1);
        }
        return $token;
    }

    /**
     * @param  string $string
     * @return string
     */
    private function encodeQuotedPrintable(string $string): string
    {
        $string = str_replace('=', '=3D', $string);
        $string = str_replace(self::KEYS, self::REPLACE_VALUES, $string);
        $string = rtrim($string);
        return $string;
    }

}