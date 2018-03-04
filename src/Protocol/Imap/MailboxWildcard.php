<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

final class MailboxWildcard
{
    private const RFC_3501_ASTRING = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x20\x1A\x1B\x1C\x1D\x1E\x1F\x22\x28\x29\x5C\x7B\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
    
    private const RFC_3501_TEXT_CHAR = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
    
    private const RFC_3501_FIXED = [
        "INBOX" => true,
    ];
    
    private const PARSE_STATE_UNQUOTED = 1;
    
    private const PARSE_STATE_QUOTED = 2;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        if (isset(self::RFC_3501_FIXED[$name])) {
            return;
        }

        $index = 0;
        $state = self::PARSE_STATE_UNQUOTED;
        while (isset($name[$index])) {
            $char = $name[$index];

            switch ($state) {
                case self::PARSE_STATE_UNQUOTED:
                    if ($char === '"') {
                        $state = self::PARSE_STATE_QUOTED;
                        break;
                    }

                    if (\strlen($char) !== \strcspn($char, self::RFC_3501_ASTRING)) {
                        throw new \InvalidArgumentException(
                            \sprintf(
                                'Invalid mailbox character %s, use a quoted string for a broader range of allowed character',
                                $char
                            )
                        );
                    }
                    break;
                case self::PARSE_STATE_QUOTED:
                    if ($char === '"') {
                        $state = self::PARSE_STATE_UNQUOTED;
                        break;
                    }

                    if (\strlen($char) !== \strcspn($char, self::RFC_3501_TEXT_CHAR)) {
                        throw new \InvalidArgumentException('Invalid mailbox name');
                    }
                    break;
            }

            $index++;
        }

        if ($state === self::PARSE_STATE_QUOTED) {
            throw new \InvalidArgumentException('Unfinished quoted literal');
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->name === '') {
            return '""';
        }

        return $this->name;
    }
}
