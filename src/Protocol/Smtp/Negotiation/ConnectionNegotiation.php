<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\NegotiationInterface;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\Mail\Protocol\Smtp\Request\StartTlsCommand;
use Genkgo\Mail\Protocol\Smtp\Response\EhloResponse;

final class ConnectionNegotiation implements NegotiationInterface
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
     * @var bool
     */
    private $insecureAllowed;

    /**
     * ConnectionNegotiation constructor.
     * @param ConnectionInterface $connection
     * @param string $ehlo
     * @param bool $insecureAllowed
     */
    public function __construct(ConnectionInterface $connection, string $ehlo, $insecureAllowed)
    {
        $this->connection = $connection;
        $this->ehlo = $ehlo;
        $this->insecureAllowed = $insecureAllowed;
    }


    /**
     * @param Client $client
     * @throws ConnectionInsecureException
     */
    public function negotiate(Client $client): void
    {
        $this->connection->receive();

        $reply = $client->request(new EhloCommand($this->ehlo));
        $reply->assertCompleted();

        $ehloResponse = new EhloResponse($reply);

        if ($ehloResponse->isAdvertising('STARTTLS')) {
            $client
                ->request(new StartTlsCommand())
                ->assertCompleted();

            $this->connection->upgrade(STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        if (!$this->insecureAllowed && empty($this->connection->getMetadata(['crypto']))) {
            throw new ConnectionInsecureException(
                'Server does not support STARTTLS. Use smtp+tls:// or to allow insecure connections use smtp+plain://'
            );
        }
    }
}