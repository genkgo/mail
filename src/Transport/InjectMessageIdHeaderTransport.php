<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class InjectMessageIdHeaderTransport implements TransportInterface
{
    /**
     * @var TransportInterface
     */
    private $decoratedTransport;

    /**
     * @var string
     */
    private $domainName;

    /**
     * @param TransportInterface $transport
     * @param string $domainName
     */
    public function __construct(TransportInterface $transport, string $domainName)
    {
        $this->decoratedTransport = $transport;
        $this->domainName = $domainName;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        if (!$message->hasHeader('Message-ID')) {
            $message = $message->withHeader(MessageId::newRandom($this->domainName));
        }

        $this->decoratedTransport->send($message);
    }
}
