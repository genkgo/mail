<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class GenericHeader implements HeaderInterface
{
    /**
     * @var HeaderName
     */
    private $name;

    /**
     * @var HeaderValue
     */
    private $value;

    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name = new HeaderName($name);
        $this->value = new HeaderValue($value);
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return $this->name;
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return $this->value;
    }
}
