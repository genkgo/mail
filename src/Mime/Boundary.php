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
            if ($ord === 10 || $ord > 127) {
                return false;
            }
            if ($ord === 13) {
                if ($i + 2 >= $total) {
                    return false;
                }
                $lf = ord($value[$i + 1]);
                $sp = ord($value[$i + 2]);
                if ($lf !== 10 || !in_array($sp, [9, 32], true)) {
                    return false;
                }
                // skip over the LF following this
                $i += 2;
            }
        }
        return true;
    }

}