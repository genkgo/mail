<?php

use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Protocol\AutomaticConnection;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\ParenthesizedList;
use Genkgo\Mail\Protocol\Imap\Request\AppendCommand;
use Genkgo\Mail\Protocol\Imap\Request\AppendDataRequest;
use Genkgo\Mail\Protocol\Imap\Request\SelectCommand;
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

$message = (new FormattedMessageFactory())
    ->withHtml('<html><body><p>Hello World</p></body></html>')
    ->createMessage()
    ->withHeader(new Subject('Hello World'))
    ->withHeader(From::fromEmailAddress('from@example.com'))
    ->withHeader(To::fromSingleRecipient('to@example.com', 'name'))
    ->withHeader(Cc::fromSingleRecipient('cc@example.com', 'name'));

$tag = $client->newTag();

$client
    ->emit(new AppendCommand($tag, 'inbox', strlen((string)$message), new ParenthesizedList()))
    ->last()
    ->assertContinuation();

$responseList = $client
    ->emit(new AppendDataRequest($tag, $message))
    ->last()
    ->assertCompletion(CompletionResult::ok())
    ->assertTagged();