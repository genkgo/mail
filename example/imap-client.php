<?php

use Genkgo\Mail\Protocol\AutomaticConnection;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\Request\FetchAllCommand;
use Genkgo\Mail\Protocol\Imap\Request\SelectCommand;
use Genkgo\Mail\Protocol\PlainTcpConnection;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

$connection = new AutomaticConnection(
    new PlainTcpConnection($config['server'],$config['port']),
    new \DateInterval('PT300S')
);

$client = new Client(
    $connection,
    [
        new ForceTlsUpgradeNegotiation($connection, CryptoConstant::getDefaultMethod(PHP_VERSION)),
        new AuthNegotiation(Client::AUTH_AUTO, $config['username'], $config['password'])
    ]
);

$client
    ->emit(new SelectCommand('inbox'))
    ->assertOk();

var_dump($client
    ->emit(new FetchAllCommand())
    ->getLines());