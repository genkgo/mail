<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\HeaderInterface;

final class ReturnPath implements HeaderInterface
{
    /**
     * @var EmailAddress
     */
    private $reversePath;

    /**
     * @param EmailAddress $reversePath
     */
    public function __construct(EmailAddress $reversePath)
    {
        $this->reversePath = $reversePath;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Return-Path');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return HeaderValue::fromEncodedString((string)$this->reversePath);
    }
}
