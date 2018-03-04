<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Header\Date;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class InjectDateHeaderTransport implements TransportInterface
{
    /**
     * @var TransportInterface
     */
    private $decoratedTransport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->decoratedTransport = $transport;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $this->decoratedTransport->send(
            $message->withHeader(
                new Date(
                    new \DateTimeImmutable()
                )
            )
        );
    }
}
