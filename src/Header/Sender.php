<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Address;
use Genkgo\Mail\HeaderInterface;

final class Sender implements HeaderInterface
{
    /**
     * @var Address
     */
    private $sender;

    /**
     * @param Address $sender
     */
    public function __construct(Address $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Sender');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue((string)$this->sender);
    }
}
