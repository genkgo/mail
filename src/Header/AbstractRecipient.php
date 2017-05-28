<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\AddressList;
use Genkgo\Mail\HeaderInterface;

/**
 * Class Recipient
 * @package Genkgo\Mail\Header
 */
abstract class AbstractRecipient implements HeaderInterface
{
    /**
     * @var AddressList
     */
    private $recipients;

    /**
     * To constructor.
     * @param AddressList $recipients
     */
    final public function __construct(AddressList $recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @return HeaderName
     */
    abstract public function getName(): HeaderName;

    /**
     * @return HeaderValue
     */
    final public function getValue(): HeaderValue
    {
        return new HeaderValue((string)$this->recipients);
    }
}