<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Response;

use Genkgo\Mail\Protocol\Smtp\Reply;

final class EhloResponse
{
    /**
     * @var string
     */
    private $greeting = '';

    /**
     * @var array
     */
    private $advertisements = [];

    /**
     * @param Reply $reply
     */
    public function __construct(Reply $reply)
    {
        $messages = $reply->getMessages();

        if (\count($messages) > 0) {
            $this->greeting = $messages[0];

            foreach (\array_slice($messages, 1) as $message) {
                $advertisement = \preg_split('/[\s]+/', $message);

                if (\count($advertisement) > 1) {
                    foreach (\array_slice($advertisement, 1) as $command) {
                        $this->advertisements[$advertisement[0] . ' ' . $command] = true;
                    }
                } else {
                    $this->advertisements[$advertisement[0]] = true;
                }
            }
        }
    }

    /**
     * @param string $command
     * @return bool
     */
    public function isAdvertising(string $command)
    {
        if ($command === '') {
            return false;
        }

        return isset($this->advertisements[$command]);
    }
}
