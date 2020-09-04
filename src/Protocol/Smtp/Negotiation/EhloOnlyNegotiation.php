<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\NegotiationInterface;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;

final class EhloOnlyNegotiation implements NegotiationInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $ehlo;

    /**
     * @param ConnectionInterface $connection
     * @param string $ehlo
     */
    public function __construct(ConnectionInterface $connection, string $ehlo)
    {
        $this->connection = $connection;
        $this->ehlo = $ehlo;
    }

    /**
     * @param Client $client
     */
    public function negotiate(Client $client): void
    {
        $client->request(new EhloCommand($this->ehlo))
            ->assertCompleted();
    }
}
