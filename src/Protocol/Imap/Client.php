<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Negotiation\ReceiveWelcomeNegotiation;
use Genkgo\Mail\Protocol\Imap\Response\AggregateResponse;
use Genkgo\Mail\Stream\LineIterator;

final class Client
{
    public const AUTH_NONE = 0;
    
    public const AUTH_PLAIN = 1;
    
    public const AUTH_LOGIN = 2;
    
    public const AUTH_AUTO = 3;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var NegotiationInterface[]
     */
    private $negotiators = [];

    /**
     * @var TagFactoryInterface
     */
    private $tagFactory;

    /**
     * @param ConnectionInterface $connection
     * @param TagFactoryInterface $tagFactory
     * @param iterable $negotiators
     */
    public function __construct(
        ConnectionInterface $connection,
        TagFactoryInterface $tagFactory,
        iterable $negotiators = []
    ) {
        $this->connection = new AppendCrlfConnection($connection);

        $this->addNegotiator(new ReceiveWelcomeNegotiation($this->connection));

        foreach ($negotiators as $negotiator) {
            $this->addNegotiator($negotiator);
        }

        $this->connection->addListener('connect', function () {
            foreach ($this->negotiators as $negotiator) {
                $negotiator->negotiate($this);
            }
        });

        $this->tagFactory = $tagFactory;
    }

    /**
     * @param NegotiationInterface $negotiation
     */
    private function addNegotiator(NegotiationInterface $negotiation)
    {
        $this->negotiators[] = $negotiation;
    }

    /**
     * @param RequestInterface $request
     * @return AggregateResponse
     */
    public function emit(RequestInterface $request): AggregateResponse
    {
        $stream = $request->toStream();

        foreach (new LineIterator($stream) as $line) {
            $this->connection->send($line);
        }

        $response = new AggregateResponse($request->getTag());

        while (!$response->hasCompleted()) {
            $response = $response->withLine($this->connection->receive());
        }

        return $response;
    }

    /**
     * @return Tag
     */
    public function newTag(): Tag
    {
        return $this->tagFactory->newTag();
    }
}
