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
     * @var string
     */
    private $ehlo;

    /**
     * @param string $ehlo
     */
    public function __construct(string $ehlo)
    {
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
