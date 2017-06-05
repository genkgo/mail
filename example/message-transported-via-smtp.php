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
use Genkgo\Mail\Protocol\Smtp\ClientFactory;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\InjectStandardHeadersTransport;
use Genkgo\Mail\Transport\SmtpTransport;

require_once __DIR__ . "/../vendor/autoload.php";
$config = require_once __DIR__ . "/config.php";

$message = (new FormattedMessageFactory())
    ->withHtml('<html><body><p><a href="http://php.net">Hello World</a></p></body></html>')
    ->withAttachment(
        ResourceAttachment::fromString(
            'Attachment text',
            'attachment.txt',
            new ContentType('plain/text')
        )
    )
    ->withEmbeddedImage(
        new EmbeddedImage(
            new StringStream(
                base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'pixel.gif',
            new ContentType('image/gif'),
            new ContentID('123456')
        )
    )
    ->createMessage()
    ->withHeader(new From(new Address(new EmailAddress($config['from']), 'name')))
    ->withHeader(new Subject('Hello World'))
    ->withHeader(new To(new AddressList([new Address(new EmailAddress($config['to']), 'name')])));

$client = ClientFactory::fromString($config['dsn'])
    ->withEhlo('hostname')
    ->newClient();

$transport = new InjectStandardHeadersTransport(
    new SmtpTransport($client, EnvelopeFactory::useExtractedHeader()),
    'hostname'
);
$transport->send($message);