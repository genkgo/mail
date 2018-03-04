<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

final class Flag
{
    private const RFC_3501_FIXED = [
        '\\Answered' => true,
        '\\Flagged' => true,
        '\\Deleted' => true,
        '\\Seen' => true,
        '\\Draft' => true,
    ];
    
    private const RFC_3501_ATOM = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x20\x1A\x1B\x1C\x1D\x1E\x1F\x22\x25\x28\x29\x2A\x5C\x5D\x7B\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";

    /**
     * @var string
     */
    private $flag;

    /**
     * @param string $flag
     */
    public function __construct(string $flag)
    {
        $this->validate($flag);

        $this->flag = $flag;
    }

    /**
     * @param Flag $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->flag === $other->flag;
    }

    /**
     * @return bool
     */
    public function isKeyword(): bool
    {
        return $this->flag[0] !== '\\';
    }

    /**
     * @return string
     */
    public function __toString():string
    {
        return $this->flag;
    }

    /**
     * @param string $flag
     */
    private function validate(string $flag): void
    {
        if ($flag === '') {
            throw new \InvalidArgumentException('Flag string is empty');
        }

        if (isset(self::RFC_3501_FIXED[$flag])) {
            return;
        }

        if ($flag[0] === '\\') {
            $this->validateAtom(\substr($flag, 1));
        } else {
            $this->validateAtom($flag);
        }
    }

    /**
     * @param string $flag
     */
    private function validateAtom(string $flag): void
    {
        if (\strcspn($flag, self::RFC_3501_ATOM) !== \strlen($flag)) {
            throw new \InvalidArgumentException(
                'You are not allowed to use any atom-specials within a flag'
            );
        }
    }
}
