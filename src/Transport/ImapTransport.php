<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\MailboxName;
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
     * @var MailboxName
     */
    private $mailbox;

    /**
     * @param Client $client
     * @param MailboxName $inbox
     */
    public function __construct(
        Client $client,
        MailboxName $inbox
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
            ->emit(new AppendCommand($tag, $this->mailbox, \strlen((string)$message)))
            ->last()
            ->assertContinuation();

        $this->client
            ->emit(new AppendDataRequest($tag, $message))
            ->last()
            ->assertCompletion(CompletionResult::ok())
            ->assertTagged();
    }
}
