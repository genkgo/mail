<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class AuthPlainCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class AuthPlainCommand extends AbstractCommand
{
    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream('AUTHENTICATE PLAIN');
    }
}