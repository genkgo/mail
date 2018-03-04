<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Exception\AbstractProtocolException;
use Genkgo\Mail\Exception\QueueIfFailedException;
use Genkgo\Mail\Exception\QueueStoreException;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\TransportInterface;

final class QueueIfFailedTransport implements TransportInterface
{
    public const QUEUED_HEADER = 'X-Queued-At';

    /**
     * @var array|TransportInterface[]
     */
    private $transports;

    /**
     * @var array|QueueInterface[]
     */
    private $queueStorage;

    /**
     * @var bool
     */
    private $useQueue = false;

    /**
     * @param TransportInterface[] $transports
     * @param QueueInterface[] $queueStorage
     */
    public function __construct(array $transports, array $queueStorage)
    {
        $this->transports = $transports;
        $this->queueStorage = $queueStorage;
    }

    /**
     * @param MessageInterface $message
     * @throws QueueIfFailedException
     */
    public function send(MessageInterface $message): void
    {
        if ($this->useQueue === false) {
            foreach ($this->transports as $sender) {
                try {
                    $sender->send($message);
                    return;
                } catch (AbstractProtocolException $e) {
                }
            }
        }

        // switch later deliveries with this transport directly to queue
        if ($this->useQueue === false) {
            $this->useQueue = true;
        }

        foreach ($this->queueStorage as $storage) {
            try {
                if (!$message->hasHeader(self::QUEUED_HEADER)) {
                    $message = $message->withHeader(
                        new GenericHeader(
                            self::QUEUED_HEADER,
                            (new \DateTimeImmutable('now'))->format('r')
                        )
                    );
                }

                $storage->store($message);
                return;
            } catch (QueueStoreException $e) {
            }
        }

        throw new QueueIfFailedException('Cannot send nor queue this e-mail message');
    }
}
