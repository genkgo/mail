<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

final class HeaderValue
{
    private const PARSE_START = 1;
    
    private const PARSE_QUOTE = 2;

    private const PARSE_CHARSET = 3;

    private const PARSE_ENCODING = 4;

    private const PARSE_VALUE = 5;

    /**
     * @var string
     */
    private $value;

    /**
     * @var array<string, HeaderValueParameter>
     */
    private $parameters = [];

    /**
     * @var array<string, bool>
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

        $headerValueAsString = \str_replace("\r\n ", ' ', \trim($headerValueAsString));

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

        $primaryValue = $values[0];

        $parameters = [];
        foreach (\array_slice($values, 1) as $parameterString) {
            try {
                $parameter = HeaderValueParameter::fromString($parameterString);
                $parameters[$parameter->getName()] = $parameter;
            } catch (\InvalidArgumentException $e) {
                $primaryValue .= '; ' . $parameterString;
            }
        }

        $headerValue = new self($primaryValue);
        $headerValue->parameters = $parameters;
        return $headerValue;
    }

    public static function parse(string $value): self
    {
        $decoded = \array_map(
            function (array $decodable) {
                if ($decodable['encoding'] === '') {
                    return $decodable['value'];
                }

                if ($decodable['encoding'] === 'B') {
                    return \base64_decode($decodable['value']);
                }

                return \iconv_mime_decode(
                    \sprintf(
                        '=?%s?%s?%s?=',
                        $decodable['charset'],
                        $decodable['encoding'],
                        $decodable['value']
                    ),
                    \ICONV_MIME_DECODE_CONTINUE_ON_ERROR,
                    $decodable['charset']
                );
            },
            self::createMimeDecodableValue($value)
        );

        $value = \implode('', $decoded);

        return self::fromString($value);
    }

    /**
     * @return array<int, array{charset: string, encoding: string, value: string}>
     */
    private static function createMimeDecodableValue(string $value): array
    {
        $sequence = '';
        $charset = '';
        $encoding = '';
        $unparsedValue = '';
        $result = [];
        $n = -1;
        $length = \strlen($value) - 1;
        $state = self::PARSE_START;

        while ($n < $length) {
            $n++;

            $char = $value[$n];

            if ($state === self::PARSE_START && $char === '=') {
                if (\trim($sequence) !== '') {
                    $result[] = [
                        'charset' => '',
                        'encoding' => '',
                        'value' => $sequence,
                    ];
                }

                $sequence = '';
            }

            if ($state === self::PARSE_CHARSET && $char === '?') {
                $state = self::PARSE_ENCODING;
                continue;
            }

            if ($state === self::PARSE_ENCODING && $char === '?') {
                $state = self::PARSE_VALUE;
                continue;
            }

            if ($state === self::PARSE_VALUE && $char === '?' && $value[$n + 1] === '=') {
                $thisResult = [
                    'charset' => $charset,
                    'encoding' => $encoding,
                    'value' => $unparsedValue,
                ];

                $remaining = \substr($value, $n + 2);
                if ($remaining === '') {
                    return [...$result, $thisResult];
                }

                $next = self::createMimeDecodableValue($remaining);
                if (\array_key_exists(0, $next)) {
                    $nextValue = $next[0];
                    if ($nextValue['encoding'] === $encoding && $nextValue['charset'] === $charset) {
                        $thisResult['value'] .= $nextValue['value'];
                        unset($next[0]);
                    }
                }

                return [...$result, $thisResult, ...$next];
            }

            match ($state) {
                self::PARSE_START => $sequence .= $char,
                self::PARSE_CHARSET => $charset .= $char,
                self::PARSE_ENCODING => $encoding .= $char,
                self::PARSE_VALUE => $unparsedValue .= $char,
                default => throw new \InvalidArgumentException('Unknown parsing state: ' . $state),
            };

            if ($state === self::PARSE_START && $sequence === '=?') {
                $state = self::PARSE_CHARSET;
            }
        }

        return [...$result, [
            'charset' => $charset,
            'encoding' => $encoding,
            'value' => $unparsedValue ?: $sequence,
        ]];
    }
}
