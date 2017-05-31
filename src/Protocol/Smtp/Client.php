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
     * @var ProtocolOptions
     */
    private $options;

    /**
     * Client constructor.
     * @param ConnectionInterface $connection
     * @param ProtocolOptions $options
     */
    public function __construct(ConnectionInterface $connection, ProtocolOptions $options)
    {
        $this->connection = $connection;
        $this->options = $options;
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
}