<?php

namespace Genkgo\TestMail\Integration;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\Dkim\CanonicalizeBodyRelaxed;
use Genkgo\Mail\Dkim\CanonicalizeHeaderRelaxed;
use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\Dkim\Sha256Signer;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\TestMail\AbstractTestCase;

final class DKIMHeaderRelaxedTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_formats_dkim_header_correctly()
    {
        $message = new PlainTextMessage('Hello World');
        $factory = new HeaderV1Factory(
            Sha256Signer::fromFile(__DIR__ . '/../Stub/Dkim/dkim.test.priv'),
            new CanonicalizeHeaderRelaxed(),
            new CanonicalizeBodyRelaxed()
        );

        $message = $message->withHeader(new Date(new \DateTimeImmutable('1/1/2017')));
        $message = $message->withHeader(new From(new Address(new EmailAddress('sender@genkgodev.com'))));
        $message = $message->withHeader(new To(new AddressList([new Address(new EmailAddress('recipient@genkgodev.com'))])));
        $message = $message->withHeader(new MessageId('testing', 'genkgodev.com'));

        $dkimHeader = $factory->factory($message, 'genkgodev.com', 'x');
        $message = $message->withHeader($dkimHeader);

        $mime = (string)$message;
        $expectedString = str_replace("\n", "\r\n",
            file_get_contents(__DIR__ . '/../Stub/Dkim/dkim_relaxed_relaxed.eml')
        );

        $this->assertEquals(
            $expectedString,
            $mime
        );
    }

}