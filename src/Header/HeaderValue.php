<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

final class HeaderValue
{
    private const PARSE_START = 1;
    
    private const PARSE_QUOTE = 2;

    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $parametersForceNewLine = [];

    /**
     * @var bool
     */
    private $needsEncoding = true;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->assertValid($value);

        $this->value = $value;
    }

    /**
     * @param HeaderValueParameter $parameter
     * @param bool $forceNewLine
     * @return HeaderValue
     */
    public function withParameter(HeaderValueParameter $parameter, bool $forceNewLine = false): HeaderValue
    {
        $clone = clone $this;
        $clone->parameters[$parameter->getName()] = $parameter;
        $clone->parametersForceNewLine[$parameter->getName()] = $forceNewLine;
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
        $value = $this->value;

        if ($this->needsEncoding) {
            $value = new OptimalEncodedHeaderValue($value);
        }

        $parameters = [];

        foreach ($this->parameters as $name => $parameter) {
            $parameters[] = \sprintf(
                ';%s%s',
                empty($this->parametersForceNewLine[$name]) ? ' ' : "\r\n ",
                (string) $parameter
            );
        }

        return \implode('', \array_merge([$value], $parameters));
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
     * @param string $value
     * @return bool
     */
    private function validate(string $value): bool
    {
        $total = \strlen($value);

        for ($i = 0; $i < $total; $i += 1) {
            $ord = \ord($value[$i]);
            if ($ord === 10) {
                return false;
            }
            if ($ord === 13) {
                if ($i + 2 >= $total) {
                    return false;
                }
                $lf = \ord($value[$i + 1]);
                $sp = \ord($value[$i + 2]);
                if ($lf !== 10 || ! \in_array($sp, [9, 32], true)) {
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

    /**
     * @param string $headerValueAsString
     * @return HeaderValue
     */
    public static function fromString(string $headerValueAsString): HeaderValue
    {
        $values = [];

        $headerValueAsString = \trim($headerValueAsString);

        $length = \strlen($headerValueAsString) - 1;
        $n = -1;
        $state = self::PARSE_START;
        $escapeNext = false;
        $sequence = '';

        while ($n < $length) {
            $n++;

            $char = $headerValueAsString[$n];

            $sequence .= $char;

            if ($char === '\\') {
                $escapeNext = true;
                continue;
            }

            if ($escapeNext) {
                $escapeNext = false;
                continue;
            }

            switch ($state) {
                case self::PARSE_QUOTE:
                    if ($char === '"') {
                        $state = self::PARSE_START;
                    }

                    break;
                default:
                    if ($char === '"') {
                        $state = self::PARSE_QUOTE;
                    }

                    if ($char === ';') {
                        $values[] = \trim(\substr($sequence, 0, -1));
                        $sequence = '';
                        $state = self::PARSE_START;
                    }
                    break;
            }
        }

        $values[] = \trim($sequence);

        $headerValue = new self($values[0]);

        $parameters = [];
        foreach (\array_slice($values, 1) as $parameterString) {
            $parameter = HeaderValueParameter::fromString($parameterString);
            $parameters[$parameter->getName()] = $parameter;
        }

        $headerValue->parameters = $parameters;
        return $headerValue;
    }
}
