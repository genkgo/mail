<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\NegotiationInterface;

final class ReceiveWelcomeNegotiation implements NegotiationInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Client $client
     */
    public function negotiate(Client $client): void
    {
        $this->connection->receive();
    }
}
