<?php

namespace Genkgo\TestMail\Integration;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\Dkim\CanonicalizeBodySimple;
use Genkgo\Mail\Dkim\CanonicalizeHeaderSimple;
use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\Dkim\Sha256Signer;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\TestMail\AbstractTestCase;

final class MessageWithDKIMHeaderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_formats_dkim_header_correctly()
    {
        $message = new PlainTextMessage('Hello World');
        $factory = new HeaderV1Factory(
            Sha256Signer::fromFile(__DIR__ . '/../Stub/Dkim/dkim.test.priv'),
            new CanonicalizeHeaderSimple(),
            new CanonicalizeBodySimple()
        );

        $message = $message->withHeader(new Date(new \DateTimeImmutable()));
        $message = $message->withHeader(new From(new Address(new EmailAddress('test@genkgo.nl'))));
        $message = $message->withHeader(new To(new AddressList([new Address(new EmailAddress('test@genkgo.nl'))])));

        $dkimHeader = $factory->factory($message, 'genkgodev.com', 'ge-test');
        $message = $message->withHeader($dkimHeader);

        $this->assertNotEquals('', (string)$message);
    }
}