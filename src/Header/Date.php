<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class Date implements HeaderInterface
{
    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @param \DateTimeImmutable $date
     */
    public function __construct(\DateTimeImmutable $date)
    {
        $this->date = $date;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Date');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue($this->date->format('r'));
    }
}
