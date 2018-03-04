<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\SmtpAuthenticationException;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\NegotiationInterface;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginCommand;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginPasswordRequest;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginUsernameRequest;
use Genkgo\Mail\Protocol\Smtp\Request\AuthPlainCommand;
use Genkgo\Mail\Protocol\Smtp\Request\AuthPlainCredentialsRequest;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\Mail\Protocol\Smtp\Response\EhloResponse;

final class AuthNegotiation implements NegotiationInterface
{
    /**
     * @var string
     */
    private $ehlo;

    /**
     * @var int
     */
    private $method;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @param int $method
     * @param string $username
     * @param string $password
     */
    public function __construct(string $ehlo, int $method, string $username, string $password)
    {
        $this->ehlo = $ehlo;
        $this->method = $method;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param Client $client
     * @throws SmtpAuthenticationException
     */
    public function negotiate(Client $client): void
    {
        $method = $this->method;
        if ($method === Client::AUTH_AUTO) {
            $reply = $client->request(new EhloCommand($this->ehlo));
            $reply->assertCompleted();

            $ehloResponse = new EhloResponse($reply);

            $options = [
                'AUTH PLAIN' => Client::AUTH_PLAIN,
                'AUTH LOGIN' => Client::AUTH_LOGIN
            ];

            foreach ($options as $capability => $auth) {
                if ($ehloResponse->isAdvertising($capability)) {
                    $method = $auth;
                }
            }

            if ($method === Client::AUTH_AUTO) {
                throw new SmtpAuthenticationException(
                    'SMTP server does not advertise one the supported AUTH methods (AUTH PLAIN / AUTH LOGIN)'
                );
            }
        }

        switch ($method) {
            case Client::AUTH_PLAIN:
                $client
                    ->request(new AuthPlainCommand())
                    ->assertIntermediate()
                    ->request(
                        new AuthPlainCredentialsRequest(
                            $this->username,
                            $this->password
                        )
                    )
                    ->assertCompleted();
                break;
            case Client::AUTH_LOGIN:
                $client
                    ->request(new AuthLoginCommand())
                    ->assertIntermediate()
                    ->request(
                        new AuthLoginUsernameRequest(
                            $this->username
                        )
                    )
                    ->assertIntermediate()
                    ->request(
                        new AuthLoginPasswordRequest(
                            $this->password
                        )
                    )
                    ->assertCompleted();
                break;
        }
    }
}
