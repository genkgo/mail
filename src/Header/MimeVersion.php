<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class MimeVersion implements HeaderInterface
{
    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('MIME-Version');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue('1.0');
    }
}
