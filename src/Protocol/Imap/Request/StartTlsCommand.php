<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class StartTlsCommand extends AbstractCommand
{
    /**
     * @return StreamInterface
     */
    public function createStream(): StreamInterface
    {
        return new StringStream('STARTTLS');
    }
}