<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class ParsedHeader implements HeaderInterface
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
     * @param HeaderName $name
     * @param HeaderValue $value
     */
    public function __construct(HeaderName $name, HeaderValue $value)
    {
        $this->name = $name;
        $this->value = $value;
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
