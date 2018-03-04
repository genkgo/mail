<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Integration;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\Dkim\CanonicalizeBodyRelaxed;
use Genkgo\Mail\Dkim\CanonicalizeHeaderRelaxed;
use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\Dkim\Parameters;
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
        $factory = new HeaderV1Factory(
            Sha256Signer::fromFile(__DIR__ . '/../Stub/Dkim/dkim.test.priv'),
            new CanonicalizeHeaderRelaxed(),
            new CanonicalizeBodyRelaxed()
        );

        $message = (new PlainTextMessage('Hello World'))
            ->withHeader(new Date(new \DateTimeImmutable('1/1/2017')))
            ->withHeader(new From(new Address(new EmailAddress('sender@genkgodev.com'))))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('recipient@genkgodev.com'))])))
            ->withHeader(new MessageId('testing', 'genkgodev.com'));

        $dkimHeader = $factory->factory($message, new Parameters('genkgodev.com', 'x'));
        $message = $message->withHeader($dkimHeader);

        $this->assertEquals(
            \file_get_contents(__DIR__ . '/../Stub/Dkim/dkim_relaxed_relaxed.eml'),
            (string)$message
        );
    }
}
