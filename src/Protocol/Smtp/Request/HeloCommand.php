<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;

final class HeloCommand implements RequestInterface
{
    /**
     * @var string
     */
    private $hostName;

    /**
     * @param string $hostName
     */
    public function __construct($hostName)
    {
        $this->hostName = $hostName;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function execute(ConnectionInterface $connection): void
    {
        $connection->send(\sprintf('HELO %s', $this->hostName));
    }
}
