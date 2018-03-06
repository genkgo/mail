<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

final class Boundary
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->assert($identifier);

        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $line
     * @return bool
     */
    public function isOpening(string $line): bool
    {
        return '--' . $this->identifier === $line;
    }

    /**
     * @param string $line
     * @return bool
     */
    public function isClosing(string $line): bool
    {
        return '--' . $this->identifier . '--' === $line;
    }

    /**
     * @return Boundary
     */
    public static function newRandom()
    {
        return new self('GenkgoMailV2Part' . \bin2hex(\random_bytes(6)));
    }

    /**
     * @param string $value
     */
    private function assert(string $value): void
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
        if ($value === '') {
            return false;
        }

        $length = \strlen($value);
        if ($length > 70) {
            return false;
        }

        if ($value[$length - 1] === ' ') {
            return false;
        }

        return \preg_match('/[^A-Za-z0-9\'\,\(\)\+\_\,\-\.\/\:\=\? ]/', $value) !== 1;
    }
}
