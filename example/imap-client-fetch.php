<?php

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Protocol\Imap\ClientFactory;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Request\FetchCommand;
use Genkgo\Mail\Protocol\Imap\Request\SelectCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Response\Command\FetchCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;

require_once __DIR__ . '/../vendor/autoload.php';

$config = (require_once __DIR__ . '/config.php')['protocol']['imap'];

$client = ClientFactory::fromString($config['dsn'])
    ->newClient();

$client
    ->emit(
        new SelectCommand(
            $client->newTag(),
            new \Genkgo\Mail\Protocol\Imap\MailboxName(
                $config['mailbox']
            )
        )
    )
    ->last()
    ->assertCompletion(CompletionResult::ok());

$responseList = $client
    ->emit(
        new FetchCommand(
            $client->newTag(),
            SequenceSet::infiniteRange(1),
            ItemList::fromString('BODY[]')
        )
    );

/** @var ItemList[] $list */
$list = [];

try {
    $index = 0;

    while (true) {
        $list[] = FetchCommandResponse::fromString($responseList->at($index)->getBody())->getDataItemList();
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