<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Negotiation\ReceiveWelcomeNegotiation;
use Genkgo\Mail\Protocol\Imap\Response\AggregateResponse;

/**
 * Class Client
 * @package Genkgo\Mail\Protocol\Imap
 */
final class Client
{
    /**
     *
     */
    public CONST AUTH_NONE = 0;
    /**
     *
     */
    public CONST AUTH_PLAIN = 1;
    /**
     *
     */
    public CONST AUTH_AUTO = 3;
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
     * Client constructor.
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
        $stream->rewind();

        $bytes = '';
        while (!$stream->eof()) {
            $bytes .= $stream->read(1000);

            $index = 0;
            while (isset($bytes[$index])) {
                if ($bytes[$index] === "\r" && isset($bytes[$index + 1]) && $bytes[$index + 1] === "\n") {
                    $line = substr($bytes, 0, $index);
                    $bytes = substr($bytes, $index + 2);
                    $index = -1;

                    $this->connection->send($line);
                }

                $index++;
            }
        }

        $this->connection->send($bytes);

        $response = new AggregateResponse($request);

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