<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\AddressList;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Request\AuthPlainCommand;
use Genkgo\Mail\Protocol\Smtp\Request\AuthPlainCredentialsRequest;
use Genkgo\Mail\Protocol\Smtp\Request\DataCommand;
use Genkgo\Mail\Protocol\Smtp\Request\DataRequest;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\Mail\Protocol\Smtp\Request\MailFromCommand;
use Genkgo\Mail\Protocol\Smtp\Request\RcptToCommand;
use Genkgo\Mail\Stream\MessageStream;
use Genkgo\Mail\TransportInterface;

final class SmtpTransport implements TransportInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;
    /**
     * @var SmtpTransportOptions
     */
    private $transportOptions;
    /**
     * @var int
     */
    private $prepared = false;
    /**
     * @var \DateTimeImmutable
     */
    private $connectedAt;

    /**
     * PhpMailTransport constructor.
     * @param Client $client
     * @param SmtpTransportOptions $transportOptions
     * @param EnvelopeFactory $envelopeFactory
     */
    public function __construct(
        Client $client,
        SmtpTransportOptions $transportOptions,
        EnvelopeFactory $envelopeFactory
    ) {
        $this->client = $client;
        $this->transportOptions = $transportOptions;
        $this->envelopeFactory = $envelopeFactory;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $this->prepare();

        $this->client
            ->request(new MailFromCommand($this->envelopeFactory->make($message)))
            ->assertCompleted();

        $addresses = $this->createAddressList($message);
        foreach ($addresses as $address) {
            $this->client
                ->request(new RcptToCommand($address->getAddress()))
                ->assertCompleted();
        }

        $this->client
            ->request(new DataCommand())
            ->assertIntermediate(new DataRequest(new MessageStream($message)))
            ->assertCompleted();

        $this->doNotExceedMaximumConnectionDuration();
    }

    /**
     * @param MessageInterface $message
     * @return AddressList
     */
    private function createAddressList(MessageInterface $message): AddressList
    {
        $list = new AddressList();

        foreach (['to', 'cc', 'bcc'] as $name) {
            foreach ($message->getHeader($name) as $header) {
                $list = $list->withList(
                    AddressList::fromString(
                        (string)$header->getValue()
                    )
                );
            }
        }

        return $list;
    }

    /**
     *
     */
    private function prepare (): void {
        if ($this->prepared) {
            return;
        }

        $this->connectedAt = new \DateTimeImmutable();

        $this->client
            ->request(new EhloCommand($this->transportOptions->getEhlo()))
            ->assertCompleted();

        if ($this->transportOptions->requiresLogin()) {
            $this->client
                ->request(new AuthPlainCommand())
                ->assertIntermediate(
                    new AuthPlainCredentialsRequest(
                        $this->transportOptions->getUsername(),
                        $this->transportOptions->getPassword()
                    )
                )->assertCompleted();
        }

        $this->prepared = true;
    }

    /**
     *
     */
    private function doNotExceedMaximumConnectionDuration()
    {
        if ($this->connectedAt->add($this->transportOptions->getMaxConnectionDuration()) > new \DateTimeImmutable()) {
            $this->client->reconnect();
            $this->prepared = false;
        }
    }

}