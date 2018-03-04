<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\NegotiationInterface;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\Mail\Protocol\Smtp\Request\HeloCommand;
use Genkgo\Mail\Protocol\Smtp\Request\StartTlsCommand;
use Genkgo\Mail\Protocol\Smtp\Response\EhloResponse;

final class TryTlsUpgradeNegotiation implements NegotiationInterface
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
     * @var int
     */
    private $crypto;

    /**
     * @param ConnectionInterface $connection
     * @param string $ehlo
     * @param int $crypto
     */
    public function __construct(
        ConnectionInterface $connection,
        string $ehlo,
        int $crypto
    ) {
        $this->connection = $connection;
        $this->ehlo = $ehlo;
        $this->crypto = $crypto;
    }

    /**
     * @param Client $client
     * @throws ConnectionInsecureException
     */
    public function negotiate(Client $client): void
    {
        if (!empty($this->connection->getMetaData(['crypto']))) {
            return;
        }

        $reply = $client->request(new EhloCommand($this->ehlo));

        if ($reply->isCommandNotImplemented()) {
            // since EHLO is not implemented, let's try HELO and then STARTTLS
            $reply = $client->request(new HeloCommand($this->ehlo));
            $reply->assertCompleted();

            try {
                $client
                    ->request(new StartTlsCommand())
                    ->assertCompleted();

                $this->connection->upgrade($this->crypto);
            } catch (AssertionFailedException $e) {
                // apparently HELO OR STARTTLS command is also not implemented
                // but failure of STARTTLS is allowed
            }
        } else {
            $reply->assertCompleted();

            $ehloResponse = new EhloResponse($reply);

            if ($ehloResponse->isAdvertising('STARTTLS')) {
                $client
                    ->request(new StartTlsCommand())
                    ->assertCompleted();

                $this->connection->upgrade($this->crypto);
            }
        }
    }
}
