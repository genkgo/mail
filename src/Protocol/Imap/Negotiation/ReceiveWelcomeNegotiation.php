<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Negotiation;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\NegotiationInterface;

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
