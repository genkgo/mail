<?php

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Protocol\AutomaticConnection;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\Request\FetchCommand;
use Genkgo\Mail\Protocol\Imap\Request\SelectCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Response\Command\FetchCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\Mail\Protocol\PlainTcpConnection;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

$connection = new AutomaticConnection(
    new PlainTcpConnection($config['server'],$config['port']),
    new \DateInterval('PT300S')
);

$client = new Client(
    $connection,
    new GeneratorTagFactory(),
    [
        new ForceTlsUpgradeNegotiation($connection, CryptoConstant::getDefaultMethod(PHP_VERSION)),
        new AuthNegotiation(Client::AUTH_AUTO, $config['username'], $config['password'])
    ]
);

$client
    ->emit(new SelectCommand($client->newTag(), new \Genkgo\Mail\Protocol\Imap\MailboxName('inbox')))
    ->last()
    ->assertCompletion(CompletionResult::ok());

$responseList = $client
    ->emit(
        new FetchCommand(
            $client->newTag(),
            SequenceSet::range(1, 2),
            ItemList::fromString('BODY[]')
        )
    );

/** @var ItemList[] $list */
$list = [];

try {
    $index = 0;

    while (true) {
        $list[] = FetchCommandResponse::fromResponse($responseList->at($index))->getDataItemList();
        $index++;
    }
} catch (\InvalidArgumentException $e) {
    $responseList
        ->last()
        ->assertCompletion(CompletionResult::ok())
        ->assertTagged();
}

foreach ($list as $item) {
    $message = GenericMessage::fromString($item->getBody());
    var_dump((string)$message->getHeader('subject')[0]->getValue());
}