<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\HeaderValue;

interface HeaderInterface
{
    /**
     * @return HeaderName
     */
    public function getName(): HeaderName;

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue;
}
