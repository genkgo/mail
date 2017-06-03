<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

/**
 * Class HeaderValue
 * @package Genkgo\Mail\Header
 */
final class HeaderValue
{
    /**
     * @var string
     */
    private $value;
    /**
     * @var array
     */
    private $parameters = [];
    /**
     * @var bool
     */
    private $needsEncoding = true;

    /**
     * HeaderValue constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->assertValid($value);

        $this->value = $value;
    }

    /**
     * @param HeaderValueParameter $parameter
     * @return HeaderValue
     */
    public function withParameter(HeaderValueParameter $parameter): HeaderValue
    {
        $clone = clone $this;
        $clone->parameters[$parameter->getName()] = $parameter;
        return $clone;
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $value = implode('; ', array_merge([$this->value], $this->parameters));

        if ($this->needsEncoding) {
            return (string) (new OptimalEncodedHeaderValue($value));
        }

        return $value;
    }

    /**
     * @param string $value
     */
    private function assertValid(string $value): void
    {
        if ($this->validate($value) === false) {
            throw new \InvalidArgumentException('Invalid header value ' . $value);
        }
    }

    /**
     * @param $value
     * @return bool
     */
    private function validate(string $value): bool
    {
        $total = strlen($value);

        for ($i = 0; $i < $total; $i += 1) {
            $ord = ord($value[$i]);
            if ($ord === 10) {
                return false;
            }
            if ($ord === 13) {
                if ($i + 2 >= $total) {
                    return false;
                }
                $lf = ord($value[$i + 1]);
                $sp = ord($value[$i + 2]);
                if ($lf !== 10 || ! in_array($sp, [9, 32], true)) {
                    return false;
                }
                // skip over the LF following this
                $i += 2;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @return HeaderValueParameter
     */
    public function getParameter(string $name): HeaderValueParameter
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        throw new \UnexpectedValueException('No parameter with name ' . $name);
    }

    /**
     * @param string $value
     * @return HeaderValue
     */
    public static function fromEncodedString(string $value): HeaderValue
    {
        $headerValue = new self($value);
        $headerValue->needsEncoding = false;
        return $headerValue;
    }
}