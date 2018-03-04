<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class EhloCapability implements CapabilityInterface
{
    /**
     * @var array
     */
    private $capabilities;

    /**
     * @var string
     */
    private $serverName;
    
    private const PROTOCOL_COMMANDS = [
        'MAIL FROM' => true,
        'EHLO' => true,
        'RCPT TO' => true,
        'RSET' => true,
        'QUIT' => true,
        'HELO' => true,
        'DATA' => true,
    ];

    /**
     * @param string $serverName
     * @param array $capabilities
     */
    public function __construct(string $serverName, array $capabilities)
    {
        $this->serverName = $serverName;
        $this->capabilities = $capabilities;
    }

    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        $command = $session->getCommand();

        $advertisements = \array_filter(
            \array_map(
                function (CapabilityInterface $capability) {
                    return $capability->advertise();
                },
                $this->capabilities
            ),
            function (string $advertisement) {
                return !isset(self::PROTOCOL_COMMANDS[$advertisement]);
            }
        );

        $messages = \array_merge(
            [$this->serverName . ' Hello ' . \substr($command, 5)],
            $advertisements
        );

        foreach ($messages as $message) {
            if (\next($messages) === false) {
                $connection->send('250 ' . $message);
            } else {
                $connection->send('250-' . $message);
            }
        }

        return $session->withState(Session::STATE_EHLO);
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'EHLO';
    }
}
