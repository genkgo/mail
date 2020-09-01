<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Negotiation;

use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Exception\SecureConnectionUpgradeException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\NegotiationInterface;
use Genkgo\Mail\Protocol\Imap\Request\CapabilityCommand;
use Genkgo\Mail\Protocol\Imap\Request\StartTlsCommand;
use Genkgo\Mail\Protocol\Imap\Response\Command\CapabilityCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;

final class TryTlsUpgradeNegotiation implements NegotiationInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $crypto;

    /**
     * @param ConnectionInterface $connection
     * @param int $crypto
     */
    public function __construct(
        ConnectionInterface $connection,
        int $crypto
    ) {
        $this->connection = $connection;
        $this->crypto = $crypto;
    }

    /**
     * @param Client $client
     * @throws ConnectionInsecureException
     */
    public function negotiate(Client $client): void
    {
        if (empty($this->connection->getMetaData(['crypto']))) {
            $responseList = $client->emit(new CapabilityCommand($client->newTag()));

            $capabilities = CapabilityCommandResponse::fromString($responseList->first()->getBody());

            $responseList
                ->last()
                ->assertCompletion(CompletionResult::ok())
                ->assertTagged();

            if ($capabilities->isAdvertising('STARTTLS')) {
                $client
                    ->emit(new StartTlsCommand($client->newTag()))
                    ->last()
                    ->assertCompletion(CompletionResult::ok())
                    ->assertTagged();

                try {
                    $this->connection->upgrade($this->crypto);
                } catch (SecureConnectionUpgradeException $e) {
                }
            }
        }
    }
}
