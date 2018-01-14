<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Protocol\Imap\Response;

final class CapabilityResponse
{
    /**
     * @var array
     */
    private $advertisements = [];

    /**
     * EhloResponse constructor.
     * @param Response $reply
     */
    public function __construct(Response $reply)
    {
        $messages = $reply->getLines();

        if (count($messages) > 0) {
            $advertisements = preg_split('/[\s]+/', reset($messages));

            $this->advertisements = array_combine(
                $advertisements,
                array_fill(0, count($advertisements), true)
            );
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