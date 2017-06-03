<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class RetryIfFailedTransport implements TransportInterface
{

    /**
     * @var TransportInterface
     */
    private $decoratedTransport;
    /**
     * @var int
     */
    private $retryCount;

    /**
     * @param TransportInterface $delegatedSender
     * @param int $retryCount
     */
    public function __construct(TransportInterface $delegatedSender, int $retryCount)
    {
        $this->decoratedTransport = $delegatedSender;
        $this->retryCount = $retryCount;
    }

    /**
     * @param MessageInterface $message
     * @throws ConnectionRefusedException
     */
    public function send(MessageInterface $message): void
    {
        for ($i = 0; $i < $this->retryCount; $i++) {
            try {
                $this->decoratedTransport->send($message);
                return;
            } catch (ConnectionRefusedException $e) {
            }
        }

        throw new ConnectionRefusedException('Cannot send e-mail, connection failed');
    }
}