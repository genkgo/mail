<?php
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Protocol\AutomaticConnection;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\Mail\Protocol\PlainTcpConnection;
use Genkgo\Mail\Transport\ImapTransport;
use Genkgo\Mail\Transport\InjectStandardHeadersTransport;

require_once __DIR__ . "/../vendor/autoload.php";
$config = require_once __DIR__ . "/config.php";

$message = (new FormattedMessageFactory())
    ->withHtml('<html><body><p>Hello World. This is ImapTransport.</p></body></html>')
    ->createMessage()
    ->withHeader(new Subject('Hello World'))
    ->withHeader(From::fromEmailAddress('from@example.com'))
    ->withHeader(To::fromSingleRecipient('to@example.com', 'name'));

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

$transport = new InjectStandardHeadersTransport(
    new ImapTransport($client, 'inbox'),
    'localhost'
);

$transport->send($message);