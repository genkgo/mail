<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;

final class Client
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * Client constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param RequestInterface $command
     * @return Reply
     */
    public function request(RequestInterface $command): Reply
    {
        $command->execute($this->connection);

        $reply = new Reply($this);
        do {
            $line = $this->connection->receive();
            list($code, $more, $message) = preg_split(
                '/([\s-]+)/',
                $line,
                2,
                PREG_SPLIT_DELIM_CAPTURE
            );

            if ($code === null) {
                throw new \RuntimeException('Unknown SMTP reply');
            }

            $reply = $reply->withLine((int)$code, trim($message));
        } while (strpos($more, '-') === 0);

        return $reply;
    }

    /**
     *
     */
    public function reconnect()
    {
        $this->connection->disconnect();
    }
}