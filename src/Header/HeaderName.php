<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

final class HeaderName
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->assert($name);

        $this->name = $name;
    }

    /**
     * @param HeaderName $headerName
     * @return bool
     */
    public function equals(self $headerName): bool
    {
        return \strcasecmp($this->name, $headerName->name) === 0;
    }

    /**
     * @param string $name
     */
    private function assert(string $name)
    {
        if ($this->validate($name) === false) {
            throw new \InvalidArgumentException(
                'Invalid header name ' . $name . '. Wrong characters used or length too long (max 74)'
            );
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function validate(string $name): bool
    {
        $tot = \strlen($name);

        if ($tot > 74) {
            return false;
        }

        for ($i = 0; $i < $tot; $i += 1) {
            $ord = \ord($name[$i]);
            if ($ord < 33 || $ord > 126 || $ord === 58) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
