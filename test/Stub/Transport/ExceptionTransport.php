<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Stub\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class ExceptionTransport implements TransportInterface
{
    /**
     * @inheritDoc
     */
    public function send(MessageInterface $message): void
    {
        throw new \RuntimeException('Exception class always throws an exception');
    }
}
