<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;

final class Session
{
    public const STATE_CONNECTED = 0;
    
    public const STATE_EHLO = 1;
    
    public const STATE_NEGOTIATION = 2;
    
    public const STATE_AUTHENTICATED = 3;
    
    public const STATE_MESSAGE = 4;
    
    public const STATE_MESSAGE_RECEIVED = 5;
    
    public const STATE_DISCONNECT = 6;

    /**
     * @var int
     */
    private $state = self::STATE_CONNECTED;

    /**
     * @var string
     */
    private $command;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var EmailAddress
     */
    private $envelope;

    /**
     * @var EmailAddress[]
     */
    private $recipients = [];

    /**
     * @param int $state
     * @return Session
     */
    public function withState(int $state): self
    {
        $clone = clone $this;
        $clone->state = $state;
        return $clone;
    }

    /**
     * @param string $command
     * @return Session
     */
    public function withCommand(string $command): self
    {
        $clone = clone $this;
        $clone->command = $command;
        return $clone;
    }

    /**
     * @param EmailAddress $envelope
     * @return Session
     */
    public function withEnvelope(EmailAddress $envelope): self
    {
        $clone = clone $this;
        $clone->state = self::STATE_MESSAGE;
        $clone->envelope = $envelope;
        return $clone;
    }

    /**
     * @param EmailAddress $recipient
     * @return Session
     */
    public function withRecipient(EmailAddress $recipient): self
    {
        $clone = clone $this;
        $clone->state = self::STATE_MESSAGE;
        $clone->recipients[] = $recipient;
        return $clone;
    }

    /**
     * @param MessageInterface $message
     * @return Session
     */
    public function withMessage(MessageInterface $message): self
    {
        $clone = clone $this;
        $clone->state = self::STATE_MESSAGE_RECEIVED;
        $clone->message = $message;
        return $clone;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        if ($this->command === null) {
            throw new \UnexpectedValueException('No command');
        }

        return $this->command;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return array|EmailAddress[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        if ($this->message === null) {
            throw new \UnexpectedValueException('No message');
        }

        return $this->message;
    }

    /**
     * @return EmailAddress
     */
    public function getEnvelope(): EmailAddress
    {
        if ($this->envelope === null) {
            throw new \UnexpectedValueException('No message');
        }

        return $this->envelope;
    }
}
