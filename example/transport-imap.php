<?php

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\ResourceAttachment;
use Genkgo\Mail\Protocol\Imap\ClientFactory;
use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\Transport\ImapTransport;
use Genkgo\Mail\Transport\InjectStandardHeadersTransport;

require_once __DIR__ . "/../vendor/autoload.php";

$config = (require_once __DIR__ . "/config.php")['transport'];

$message = (new FormattedMessageFactory())
    ->withHtml($config['html'])
    ->withAttachment(
        ResourceAttachment::fromString(
            $config['attachment'],
            'attachment.txt',
            new ContentType('plain/text')
        )
    )
    ->withEmbeddedImage(
        new EmbeddedImage(
            new StringStream($config['image']),
            'pixel.gif',
            new ContentType('image/gif'),
            new ContentID('123456')
        )
    )
    ->createMessage()
    ->withHeader(new From(new Address(new EmailAddress($config['from']), $config['from-name'])))
    ->withHeader(new Subject($config['subject']))
    ->withHeader(new To(new AddressList([new Address(new EmailAddress($config['to']), $config['to-name'])])));

$transport = new InjectStandardHeadersTransport(
    new ImapTransport(
        ClientFactory::fromString($config['imap'])
            ->newClient(),
        new MailboxName($config['mailbox'])
    ),
    $config['ehlo']
);

$transport->send($message);