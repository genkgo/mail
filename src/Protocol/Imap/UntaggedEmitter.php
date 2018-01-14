<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Protocol\ConnectionInterface;

final class UntaggedEmitter implements EmitterInterface
{

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * UntaggedEmitter constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param RequestInterface $request
     * @return Response
     */
    public function emit(RequestInterface $request): Response
    {
        $stream = $request->toStream();
        $stream->rewind();

        $bytes = '';
        while (!$stream->eof()) {
            $bytes .= $stream->read(1000);

            $index = 0;
            while (isset($bytes[$index])) {
                if ($bytes[$index] === "\r" && isset($bytes[$index + 1]) && $bytes[$index + 1] === "\n") {
                    $line = substr($bytes, 0, $index);
                    $bytes = substr($bytes, $index + 2);
                    $index = -1;

                    $this->connection->send($line);
                }

                $index++;
            }
        }

        $this->connection->send($bytes);

        $reply = new Response($this, $request);
        do {
            $line = $this->connection->receive();
            $reply = $reply->withLine($line);
        } while (isset($line[0]) && $line[0] === '*');

        return $reply;
    }
}