<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class CapabilityCommand implements RequestInterface
{
    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function execute(ConnectionInterface $connection): void
    {
        $connection->send('CAPABILITY');
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return new StringStream('CAPABILITY');
    }
}