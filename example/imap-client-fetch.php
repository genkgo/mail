<?php

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Protocol\Imap\ClientFactory;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Request\FetchCommand;
use Genkgo\Mail\Protocol\Imap\Request\SelectCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Response\Command\ParsedFetchCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;

require_once __DIR__ . '/../vendor/autoload.php';

$config = (require_once __DIR__ . '/config.php')['protocol']['imap'];

$client = ClientFactory::fromString($config['dsn'])
    ->newClient();

$responseSelect = $client
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
            ItemList::fromString('(BODY[])')
        )
    );

/** @var ItemList[] $list */
$list = [];

try {
    $index = 0;

    while (true) {
        $list[] = GenericMessage::fromString(
            $responseList
                ->at($index)
                ->assertParsed(ParsedFetchCommandResponse::class)
                ->getItemList()
                ->getBody()
        );

        $index++;
    }
} catch (AssertionFailedException $e) {
    $responseList
        ->last()
        ->assertCompletion(CompletionResult::ok())
        ->assertTagged();
}

var_dump($list);exit;
