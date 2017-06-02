<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\AddressList;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\ClientFactoryInterface;
use Genkgo\Mail\Protocol\Smtp\Request\DataCommand;
use Genkgo\Mail\Protocol\Smtp\Request\DataRequest;
use Genkgo\Mail\Protocol\Smtp\Request\MailFromCommand;
use Genkgo\Mail\Protocol\Smtp\Request\RcptToCommand;
use Genkgo\Mail\Stream\MessageStream;
use Genkgo\Mail\TransportInterface;

final class SmtpTransport implements TransportInterface
{
    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;
    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * PhpMailTransport constructor.
     * @param ClientFactoryInterface $clientFactory
     * @param EnvelopeFactory $envelopeFactory
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
        EnvelopeFactory $envelopeFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->envelopeFactory = $envelopeFactory;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $client = $this->clientFactory->newClient();

        $client
            ->request(new MailFromCommand($this->envelopeFactory->make($message)))
            ->assertCompleted();

        $addresses = $this->createAddressList($message);
        foreach ($addresses as $address) {
            $client
                ->request(new RcptToCommand($address->getAddress()))
                ->assertCompleted();
        }

        $client
            ->request(new DataCommand())
            ->assertIntermediate()
            ->request(new DataRequest(new MessageStream($message)))
            ->assertCompleted();
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
}