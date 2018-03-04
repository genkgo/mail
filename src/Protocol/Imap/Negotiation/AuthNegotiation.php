<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Negotiation;

use Genkgo\Mail\Exception\ImapAuthenticationException;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\NegotiationInterface;
use Genkgo\Mail\Protocol\Imap\Request\AuthPlainCommand;
use Genkgo\Mail\Protocol\Imap\Request\AuthPlainCredentialsRequest;
use Genkgo\Mail\Protocol\Imap\Request\CapabilityCommand;
use Genkgo\Mail\Protocol\Imap\Request\LoginCommand;
use Genkgo\Mail\Protocol\Imap\Response\Command\CapabilityCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;

final class AuthNegotiation implements NegotiationInterface
{
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
    public function __construct(int $method, string $username, string $password)
    {
        $this->method = $method;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param Client $client
     * @throws ImapAuthenticationException
     */
    public function negotiate(Client $client): void
    {
        $method = $this->method;
        if ($method === Client::AUTH_AUTO) {
            $responseList = $client->emit(new CapabilityCommand($client->newTag()));

            $capabilities = CapabilityCommandResponse::fromString($responseList->first()->getBody());

            $responseList
                ->last()
                ->assertCompletion(CompletionResult::ok())
                ->assertTagged();

            $options = [
                'AUTH=PLAIN' => Client::AUTH_PLAIN,
            ];

            foreach ($options as $capability => $auth) {
                if ($capabilities->isAdvertising($capability)) {
                    $method = $auth;
                }
            }

            if ($method === Client::AUTH_AUTO && !$capabilities->isAdvertising('LOGINDISABLED')) {
                $method = Client::AUTH_LOGIN;
            }

            if ($method === Client::AUTH_AUTO) {
                throw new ImapAuthenticationException(
                    'IMAP server does not advertise one the supported AUTH methods (AUTH PLAIN)'
                );
            }
        }

        switch ($method) {
            case Client::AUTH_PLAIN:
                $tag = $client->newTag();
                $client
                    ->emit(new AuthPlainCommand($tag))
                    ->first()
                    ->assertContinuation();

                $client
                    ->emit(
                        new AuthPlainCredentialsRequest(
                            $tag,
                            $this->username,
                            $this->password
                        )
                    )
                    ->last()
                    ->assertCompletion(CompletionResult::ok())
                    ->assertTagged();
                break;
            case Client::AUTH_LOGIN:
                $tag = $client->newTag();
                $client
                    ->emit(new LoginCommand($tag, $this->username, $this->password))
                    ->last()
                    ->assertTagged()
                    ->assertCompletion(CompletionResult::ok());
                break;
        }
    }
}
