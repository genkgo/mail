<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class AuthPlainCommand implements RequestInterface
{
    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return new StringStream('AUTHENTICATE PLAIN');
    }
}