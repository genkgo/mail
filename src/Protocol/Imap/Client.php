<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Negotiation\ReceiveWelcomeNegotiation;

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
     * @var \Iterator
     */
    private $tagList;

    /**
     * Client constructor.
     * @param ConnectionInterface $connection
     * @param iterable $negotiators
     */
    public function __construct(ConnectionInterface $connection, iterable $negotiators = [])
    {
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

        $this->tagList = $this->newTagList();
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
        $this->tagList->next();

        $request = $request->withTag($this->tagList->current());
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
     * @return \Generator
     */
    private function newTagList(): \Generator
    {
        $i = 0;

        while (true) {
            $i++;
            yield new Tag('TAG' . $i);
        }
    }
}