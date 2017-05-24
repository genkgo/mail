<?php

namespace Genkgo\Mail\Unit;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\HtmlOnlyMessage;

final class HtmlOnlyMessageTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_correctly_produces_message_string() {
        $message = (new HtmlOnlyMessage('<html><body><p>Hello World</p></body></html>'))
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')))
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To([new Address(new EmailAddress('me@example.com'), 'me')])))
            ->withHeader((new Cc([new Address(new EmailAddress('other@example.com'), 'other')])))
        ;

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../Stub/HtmlOnlyMessageTest/message.eml'),
            (string) $message
        );
    }

}