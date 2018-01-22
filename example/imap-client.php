<?php

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\AutomaticConnection;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\DataLogConnection;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\CompletionResult;
use Genkgo\Mail\Protocol\Imap\MessageDataItemList;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\Request\FetchCommand;
use Genkgo\Mail\Protocol\Imap\Request\SelectCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\PlainTcpConnection;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

$connection = new AutomaticConnection(
    new DataLogConnection(
        new PlainTcpConnection($config['server'],$config['port']),
        new class implements LoggerInterface {

            use \Psr\Log\LoggerTrait;

            public function log($level, $message, array $context = array())
            {
                echo $message;
            }

        }
    ),
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
    ->last()
    ->assertCompletion(CompletionResult::ok());

$responseList = $client
    ->emit(
        new FetchCommand(
            (new SequenceSet(1))->withLast(2),
            (new MessageDataItemList())->withName('BODY[]')
        )
    );

$list = [];

try {
    $index = 0;

    $list[] = MessageDataItemList::fromString(
        $responseList
            ->at(++$index)
            ->assertCommand('FETCH')
            ->getBody()
    );
} catch (AssertionFailedException $e) {
    $responseList
        ->at($index)
        ->assertCompletion(CompletionResult::ok())
        ->assertTagged();
}
