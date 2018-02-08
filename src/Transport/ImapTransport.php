<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\ParenthesizedList;
use Genkgo\Mail\Protocol\Imap\Request\AppendCommand;
use Genkgo\Mail\Protocol\Imap\Request\AppendDataRequest;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\Mail\TransportInterface;

final class ImapTransport implements TransportInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $mailbox;

    /**
     * SmtpTransport constructor.
     * @param Client $client
     * @param string $inbox
     */
    public function __construct(
        Client $client,
        string $inbox
    ) {
        $this->client = $client;
        $this->mailbox = $inbox;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $tag = $this->client->newTag();

        $this->client
            ->emit(new AppendCommand($tag, $this->mailbox, strlen((string)$message), new ParenthesizedList()))
            ->last()
            ->assertContinuation();

        $this->client
            ->emit(new AppendDataRequest($tag, $message))
            ->last()
            ->assertCompletion(CompletionResult::ok())
            ->assertTagged();
    }
}