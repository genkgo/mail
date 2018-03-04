<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Negotiation\ReceiveWelcomeNegotiation;

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
    }

    /**
     * @param NegotiationInterface $negotiation
     */
    private function addNegotiator(NegotiationInterface $negotiation)
    {
        $this->negotiators[] = $negotiation;
    }

    /**
     * @param RequestInterface $command
     * @return Reply
     */
    public function request(RequestInterface $command): Reply
    {
        $command->execute($this->connection);

        $reply = new Reply($this);
        do {
            $line = $this->connection->receive();
            list($code, $more, $message) = \preg_split('/([\s-]+)/', $line, 2, PREG_SPLIT_DELIM_CAPTURE);
            $reply = $reply->withLine((int)$code, \trim($message));
        } while (\strpos($more, '-') === 0);

        return $reply;
    }
}
