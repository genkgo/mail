<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\Dkim\Parameters;
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
     * @var Parameters
     */
    private $parameters;

    /**
     * @param TransportInterface $transport
     * @param HeaderV1Factory $headerFactory
     * @param Parameters $parameters
     */
    public function __construct(
        TransportInterface $transport,
        HeaderV1Factory $headerFactory,
        Parameters $parameters
    ) {
        $this->transport = $transport;
        $this->headerFactory = $headerFactory;
        $this->parameters = $parameters;
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
                    $this->parameters->withSignatureTimestamp(new \DateTimeImmutable('now'))
                )
            )
        );
    }
}
