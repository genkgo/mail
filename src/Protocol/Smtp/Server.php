<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionBrokenException;
use Genkgo\Mail\Exception\ConnectionTimeoutException;
use Genkgo\Mail\Exception\ConnectionClosedException;
use Genkgo\Mail\Exception\UnknownSmtpCommandException;
use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\ConnectionListenerInterface;
use Genkgo\Mail\Protocol\Smtp\Capability\EhloCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\QuitCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\ResetCapability;
use Genkgo\Mail\Protocol\TrimCrlfConnection;

final class Server
{
    /**
     * @var ConnectionListenerInterface
     */
    private $listener;

    /**
     * @var CapabilityInterface[]
     */
    private $capabilities = [];

    /**
     * @var string
     */
    private $serverName;

    /**
     * @param ConnectionListenerInterface $connection
     * @param array $capabilities
     * @param string $serverName
     */
    public function __construct(ConnectionListenerInterface $connection, array $capabilities, string $serverName)
    {
        $this->listener = $connection;
        $this->serverName = $serverName;

        $this->capabilities = \array_merge(
            $capabilities,
            [
                new EhloCapability($this->serverName, $capabilities),
                new QuitCapability(),
                new ResetCapability()
            ]
        );
    }
    
    public function start(): void
    {
        while ($connection = $this->listener->listen()) {
            $connection = new TrimCrlfConnection(new AppendCrlfConnection($connection));
            $connection->send('220 Welcome to Genkgo Mail Server');

            $this->transport(
                $connection,
                function (ConnectionInterface $connection) {
                    $session = new Session();

                    while ($session = $session->withCommand($connection->receive())) {
                        try {
                            $session = $this->handleCommand($connection, $session);
                        } catch (UnknownSmtpCommandException $e) {
                            $connection->send('500 unrecognized command');
                        }

                        if ($session->getState() === Session::STATE_DISCONNECT) {
                            break;
                        }
                    }
                }
            );
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @param \Closure $callback
     */
    private function transport(ConnectionInterface $connection, \Closure $callback)
    {
        try {
            $callback($connection);
        } catch (ConnectionTimeoutException $e) {
            $connection->send('421 command timeout - closing connection');
            $connection->disconnect();
        } catch (ConnectionBrokenException $e) {
            try {
                $connection->send('554 transaction failed, unexpected value - closing connection');
            } catch (\Exception $e) {
            }

            $connection->disconnect();
        } catch (ConnectionClosedException $e) {
            $connection->disconnect();
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     * @throws UnknownSmtpCommandException
     */
    private function handleCommand(ConnectionInterface $connection, Session $session): Session
    {
        $command = $session->getCommand();

        foreach ($this->capabilities as $capability) {
            $advertised = $capability->advertise();
            if (\substr($command, 0, \strlen($advertised)) === $advertised) {
                return $capability->manifest($connection, $session);
            }
        }

        throw new UnknownSmtpCommandException('Cannot handle command');
    }
}
