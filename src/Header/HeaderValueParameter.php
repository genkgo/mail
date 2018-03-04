<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

final class HeaderValueParameter
{
    public const RFC_822_T_SPECIAL = "\x28\x29\x3C\x3E\x40\x2C\x3B\x3A\x5C\x22\x5B\x5D\x2E";
    
    private const ANY_ASCII_EXCEPT_SPACE_CTL = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $specials = self::RFC_822_T_SPECIAL;

    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        if ($name === '' || \strcspn($name, self::ANY_ASCII_EXCEPT_SPACE_CTL) !== \strlen($name)) {
            throw new \InvalidArgumentException('Incorrect name');
        }

        if (\strcspn($value, self::ANY_ASCII_EXCEPT_SPACE_CTL) !== \strlen($value)) {
            throw new \InvalidArgumentException('Incorrect value');
        }

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @param string $specials
     * @return HeaderValueParameter
     */
    public function withSpecials(string $specials)
    {
        $clone = clone $this;
        $clone->specials = $specials;
        return $clone;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (\strcspn($this->value, $this->specials) !== \strlen($this->value)) {
            return \sprintf('%s="%s"', $this->name, $this->value);
        }

        return \sprintf('%s=%s', $this->name, $this->value);
    }

    /**
     * @param string $parameterString
     * @return HeaderValueParameter
     */
    public static function fromString(string $parameterString): HeaderValueParameter
    {
        $nameValue = \explode('=', $parameterString);
        if (\count($nameValue) !== 2) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid parameter string value %s', $parameterString)
            );
        }

        [$name, $value] = $nameValue;

        if ($value[0] === '"' && $value[-1] === '"') {
            $value = \substr($value, 1, -1);
        }

        return new self($name, $value);
    }
}
