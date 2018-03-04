<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

final class Cc extends AbstractRecipient
{
    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Cc');
    }
}
