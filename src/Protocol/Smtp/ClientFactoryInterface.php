<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

interface ClientFactoryInterface
{

    /**
     * @return Client
     */
    public function newClient(): Client;

}