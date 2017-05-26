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
     * Boundary constructor.
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
     * @return Boundary
     */
    public static function newRandomBoundary()
    {
        return new self('_part_' . bin2hex(random_bytes(6)));
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
     * @param $value
     * @return bool
     */
    private function validate(string $value): bool
    {
        $total = strlen($value);
        for ($i = 0; $i < $total; $i += 1) {
            $ord = ord($value[$i]);
            // bare LF means we aren't valid
            if ($ord === 10 || $ord === 13) {
                return false;
            }
        }
        return true;
    }

}