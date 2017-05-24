<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\Exception\QueueStoreException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\MessageInterface;
use Predis\Client;
use Predis\Connection\ConnectionException;

final class RedisQueue implements QueueInterface
{

    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $key;

    /**
     * RedisQueue constructor.
     * @param Client $client
     * @param string $key
     */
    public function __construct(Client $client, string $key)
    {
        $this->client = $client;
        $this->key = $key;
    }

    /**
     * @param MessageInterface $message
     * @throws QueueStoreException
     */
    public function store(MessageInterface $message): void
    {
        try {
            $this->client->rpush($this->key, (string)$message);
        } catch (ConnectionException $e) {
            throw new QueueStoreException('Cannot add message to redis queue: ' . $e->getMessage());
        }
    }

    /**
     * @return MessageInterface
     * @throws EmptyQueueException
     * @throws QueueStoreException
     */
    public function fetch(): MessageInterface
    {
        try {
            $message = $this->client->lpop($this->key);
            if ($message) {
                return GenericMessage::fromString($message);
            }

            throw new EmptyQueueException();
        } catch (ConnectionException $e) {
            throw new QueueStoreException('Cannot add message to redis queue ' . $e->getMessage());
        }
    }
}