<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class AggregateTransport implements TransportInterface
{
    /**
     * @var iterable|TransportInterface[]
     */
    private $transports;

    /**
     * @param iterable|TransportInterface[] $transports
     */
    public function __construct(iterable $transports)
    {
        $this->transports = $transports;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        foreach ($this->transports as $transport) {
            $transport->send($message);
        }
    }
}
