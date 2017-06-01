<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginCommand;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginPasswordRequest;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginUsernameRequest;
use Genkgo\Mail\Protocol\Smtp\Request\AuthPlainCommand;
use Genkgo\Mail\Protocol\Smtp\Request\AuthPlainCredentialsRequest;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\Mail\Protocol\Smtp\Request\StartTlsCommand;
use Genkgo\Mail\Protocol\Smtp\Response\EhloResponse;

/**
 * Class ClientFactory
 * @package Genkgo\Mail\Protocol\Smtp
 */
final class ClientFactory
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
    public CONST AUTH_LOGIN = 2;
    /**
     *
     */
    public CONST AUTH_AUTO = 3;
    /**
     *
     */
    private CONST AUTH_ENUM = [self::AUTH_NONE, self::AUTH_PLAIN, self::AUTH_LOGIN, self::AUTH_AUTO];
    /**
     * @var ConnectionInterface
     */
    private $connection;
    /**
     * @var string
     */
    private $host = 'localhost';
    /**
     * @var int
     */
    private $port = 25;
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var float
     */
    private $timeout = 1;
    /**
     * @var string
     */
    private $username = '';
    /**
     * @var string
     */
    private $ehlo = '127.0.0.1';
    /**
     * @var
     */
    private $authMethod = self::AUTH_NONE;

    /**
     * ClientFactory constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $host
     * @param int $port
     * @return ClientFactory
     */
    public function withHost(string $host, int $port = 25): ClientFactory
    {
        $clone = clone $this;
        $clone->host = $host;
        $clone->port = $port;
        return $clone;
    }

    /**
     * @param float $connectionTimeout
     * @return ClientFactory
     */
    public function withTimeout(float $connectionTimeout): ClientFactory
    {
        $clone = clone $this;
        $clone->timeout = $connectionTimeout;
        return $clone;
    }

    /**
     * @param int $method
     * @param string $password
     * @param string $username
     * @return ClientFactory
     */
    public function withAuthentication(int $method, string $password, string $username): ClientFactory
    {
        if (!in_array($method, self::AUTH_ENUM)) {
            throw new \InvalidArgumentException('Invalid authentication method');
        }

        $clone = clone $this;
        $clone->authMethod = $method;
        $clone->username = $username;
        $clone->password = $password;
        return $clone;
    }

    /**
     * @param string $ehlo
     * @return ClientFactory
     */
    public function withEhlo(string $ehlo): ClientFactory
    {
        $clone = clone $this;
        $clone->ehlo = $ehlo;
        return $clone;
    }

    /**
     * @return Client
     */
    public function newClient(): Client
    {
        $client = new Client($this->connection);

        $ehloResponse = new EhloResponse(
            $client
                ->request(new EhloCommand($this->ehlo))
                ->assertCompleted()
        );

        if ($ehloResponse->isAdvertising('STARTTLS')) {
            $client
                ->request(new StartTlsCommand())
                ->assertCompleted();

            $this->connection->upgrade(STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        $method = $this->authMethod;
        if ($method === ClientFactory::AUTH_AUTO) {
            $options = [
                'AUTH PLAIN' => ClientFactory::AUTH_PLAIN,
                'AUTH LOGIN' => ClientFactory::AUTH_LOGIN
            ];

            foreach ($options as $advertisement => $auth) {
                if ($ehloResponse->isAdvertising($advertisement)) {
                    $method = $auth;
                }
            }
        }

        switch ($method) {
            case ClientFactory::AUTH_PLAIN:
                $client
                    ->request(new AuthPlainCommand())
                    ->assertIntermediate(
                        new AuthPlainCredentialsRequest(
                            $this->username,
                            $this->password
                        )
                    )
                    ->assertCompleted();
                break;
            case ClientFactory::AUTH_LOGIN:
                $client
                    ->request(new AuthLoginCommand())
                    ->assertIntermediate(
                        new AuthLoginUsernameRequest(
                            $this->username
                        )
                    )
                    ->assertIntermediate(
                        new AuthLoginPasswordRequest(
                            $this->password
                        )
                    )
                    ->assertCompleted();
                break;
        }

        return $client;
    }
}
