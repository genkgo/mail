<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\AddressList;
use Genkgo\Mail\Header\Sender;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class InjectSenderHeaderTransport implements TransportInterface
{
    /**
     * @var TransportInterface
     */
    private $decoratedTransport;

    /**
     * InjectDateHeaderTransport constructor.
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
        if ($message->hasHeader('from')) {
            $message = $message
                ->withHeader(
                    new Sender(
                        AddressList::fromString(
                            $message->getHeader('from')[0]->getValue()->getRaw()
                        )->first()
                    )
                );
        }

        $this->decoratedTransport->send($message);
    }
}