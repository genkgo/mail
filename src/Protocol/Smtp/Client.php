<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Stream\MessageStream;

final class Client
{
    /**
     *
     */
    private const STATE_NONE = 0;
    /**
     *
     */
    private const STATE_WELCOMED = 1;
    /**
     *
     */
    private const STATE_NEGOTIATED = 2;
    /**
     *
     */
    private const STATE_AUTHENTICATED = 3;
    /**
     *
     */
    private const STATE_TRANSPORTED = 4;
    /**
     * @var ConnectionInterface
     */
    private $connection;
    /**
     * @var int
     */
    private $state = self::STATE_NONE;
    /**
     * @var ProtocolOptions
     */
    private $options;

    /**
     * Client constructor.
     * @param ConnectionInterface $connection
     * @param ProtocolOptions $options
     */
    public function __construct(ConnectionInterface $connection, ProtocolOptions $options)
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    /**
     * @param MessageInterface $message
     * @param EmailAddress $envelope
     * @param AddressList $recipients
     */
    public function deliver(MessageInterface $message, EmailAddress $envelope, AddressList $recipients): void
    {
        $this->ehlo();
        $this->negotiate();
        $this->authenticate();

        if ($this->state === self::STATE_TRANSPORTED) {
            (new Command($this->connection, 'RSET'))
                ->withExpectCodes([250, 220])
                ->execute();
        }

        (new Command($this->connection, sprintf('MAIL FROM:<%s>', (string)$envelope)))
            ->withExpectCodes([250, 251])
            ->withCallback(function (){
                $this->state = self::STATE_TRANSPORTED;
            })
            ->execute();


        foreach ($recipients as $recipient) {
            (new Command($this->connection, sprintf('RCPT TO:<%s>', (string)$recipient->getAddress())))
                ->withExpectCodes([250, 251])
                ->withCallback(function (){
                    $this->state = self::STATE_TRANSPORTED;
                })
                ->execute();
        }

        (new Command($this->connection, 'DATA'))
            ->withExpectCode(354)
            ->withCallback(function (){
                $this->state = self::STATE_TRANSPORTED;
            })
            ->execute();


        $stream = new MessageStream($message);
        while (!$stream->eof()) {
            $bytes = $stream->read(1000);
            $lines = explode("\r\n", $bytes);
            foreach ($lines as $line) {
                $line = rtrim($line, "\r");
                if (isset($line[0]) && $line[0] === '.') {
                    $line = '.' . $line;
                }

                $this->connection->send($line . "\r\n");
            }
        }

        (new Command($this->connection, '.'))
            ->withExpectCode(250)
            ->withCallback(function (){
                $this->state = self::STATE_TRANSPORTED;
            })
            ->execute();
    }

    /**
     *
     */
    private function ehlo(): void
    {
        if ($this->state > self::STATE_WELCOMED) {
            return;
        }

        (new Command($this->connection, sprintf('EHLO %s', $this->options->getEhlo())))
            ->withExpectCode(250)
            ->withCallback(function () {
                $this->state = self::STATE_WELCOMED;
            })
            ->execute();
    }

    /**
     *
     */
    private function negotiate(): void
    {
        if ($this->state > self::STATE_NEGOTIATED) {
            return;
        }

        try {
            (new Command($this->connection, 'STARTTLS'))
                ->withExpectCode(220)
                ->withCallback(function () {
                    $this->state = self::STATE_NEGOTIATED;
                })
                ->withCallback(function () {
                    $this->connection = $this->connection->upgrade(STREAM_CRYPTO_METHOD_TLS_CLIENT);
                })
                ->execute();
        } catch (\RuntimeException $e) {
            $this->state = self::STATE_NEGOTIATED;
        }
    }

    /**
     *
     */
    private function authenticate(): void
    {
        if ($this->state > self::STATE_AUTHENTICATED) {
            return;
        }

        if ($this->options->getUsername() !== '' && $this->options->getPassword() !== '') {
            (new Command($this->connection, 'AUTH PLAIN'))
                ->withExpectCode(334)
                ->withCallback(function () {
                    $this->state = self::STATE_NEGOTIATED;
                })
                ->withCallback(function () {
                    $encoded = base64_encode(
                        sprintf("\0%s\0%s", $this->options->getUsername(), $this->options->getPassword())
                    );

                    (new Command($this->connection, $encoded))
                        ->withExpectCode(235)
                        ->withCallback(function () {
                            $this->state = self::STATE_NEGOTIATED;
                        })
                        ->execute();
                })
                ->execute();
        }
    }
}