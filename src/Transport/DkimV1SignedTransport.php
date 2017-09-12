<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class DkimV1SignedTransport implements TransportInterface
{
    /**
     * @var TransportInterface
     */
    private $transport;
    /**
     * @var HeaderV1Factory
     */
    private $headerFactory;
    /**
     * @var string
     */
    private $domain;
    /**
     * @var string
     */
    private $selector;

    /**
     * DkimSignedTransport constructor.
     * @param TransportInterface $transport
     * @param HeaderV1Factory $headerFactory
     * @param string $domain
     * @param string $selector
     */
    public function __construct(
        TransportInterface $transport,
        HeaderV1Factory $headerFactory,
        string $domain,
        string $selector
    ) {
        $this->transport = $transport;
        $this->headerFactory = $headerFactory;
        $this->domain = $domain;
        $this->selector = $selector;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $this->transport->send(
            $message->withHeader(
                $this->headerFactory->factory(
                    $message,
                    $this->domain,
                    $this->selector
                )
            )
        );
    }
}