<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class CapabilityCommand extends AbstractCommand
{
    /**
     * @return StreamInterface
     */
    public function createStream(): StreamInterface
    {
        return new StringStream('CAPABILITY');
    }
}