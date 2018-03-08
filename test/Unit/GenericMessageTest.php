<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\MimeVersion;
use Genkgo\Mail\Stream\EmptyStream;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class GenericMessageTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $message = new GenericMessage();

        $this->assertNotSame($message, $message->withBody(new EmptyStream()));
        $this->assertNotSame($message, $message->withHeader(new GenericHeader('X', 'Y')));
        $this->assertNotSame($message, $message->withAddedHeader(new GenericHeader('X', 'Y')));
        $this->assertNotSame($message, $message->withoutHeader('X'));
    }

    /**
     * @test
     */
    public function it_has_case_insensitive_headers()
    {
        $message = (new GenericMessage())
            ->withHeader(new GenericHeader('X', 'Y'))
            ->withHeader(new GenericHeader('wEiRd-CasEd-HeaDer', 'value'));

        $this->assertEquals('Y', (string)$message->getHeader('X')[0]->getValue());
        $this->assertEquals('Y', (string)$message->getHeader('x')[0]->getValue());
        $this->assertEquals('value', (string)$message->getHeader('wEiRd-CasEd-HeaDer')[0]->getValue());
        $this->assertEquals('value', (string)$message->getHeader('weird-cased-header')[0]->getValue());
        $this->assertEquals('value', (string)$message->getHeader('WEIRD-CASED-HEADER')[0]->getValue());
        $this->assertEquals('value', (string)$message->getHeader('Weird-Cased-Header')[0]->getValue());
        $this->assertEquals('value', $message->getHeaders()['weird-cased-header'][0]->getValue());
        $this->assertTrue($message->hasHeader('wEiRd-CasEd-HeaDer'));
        $this->assertTrue($message->hasHeader('weird-cased-header'));
        $this->assertTrue($message->hasHeader('WEIRD-CASED-HEADER'));
        $this->assertTrue($message->hasHeader('Weird-Cased-Header'));
    }

    /**
     * @test
     */
    public function it_return_an_empty_array_when_there_are_no_headers()
    {
        $message = (new GenericMessage());

        $this->assertEquals([], $message->getHeader('to'));
        $this->assertEquals([], $message->getHeader('x-custom'));
    }

    /**
     * @test
     */
    public function it_orders_headers()
    {
        $message1 = (new GenericMessage())
            ->withHeader(new MimeVersion())
            ->withHeader(new GenericHeader('Subject', 'Value'));

        $message2 = (new GenericMessage())
            ->withHeader(new GenericHeader('Subject', 'Value'))
            ->withHeader(new MimeVersion());

        $this->assertEquals((string) $message1, (string) $message2);
        $this->assertContains("Subject: Value\r\nMIME-Version: 1.0", (string)$message1);
    }

    /**
     * @test
     */
    public function it_can_parse_a_message_from_string()
    {
        $message = (new GenericMessage())
            ->withHeader(new GenericHeader('Subject', 'Hello World'))
            ->withHeader(new GenericHeader('To', 'me@example.com'))
            ->withHeader(new GenericHeader('From', 'other@example.com'))
        ;

        $this->assertEquals(
            (string) $message,
            (string) GenericMessage::fromString((string) $message)
        );
    }

    /**
     * @test
     */
    public function it_can_parse_a_message_with_header_folding_from_string()
    {
        $message = (new GenericMessage())
            ->withHeader(new GenericHeader('Subject', 'Hello World'))
            ->withHeader(new GenericHeader('To', 'me@example.com'))
            ->withHeader(new GenericHeader('From', 'other@example.com'))
            ->withHeader(new GenericHeader('X-Custom', \str_repeat('tëst', 50)))
            ->withBody(new AsciiEncodedStream('Hello World'))
        ;

        $this->assertEquals(
            (string) $message,
            (string) GenericMessage::fromString((string) $message)
        );
    }

    /**
     * @test
     */
    public function it_can_parse_a_message_with_tabbed_header_folding_from_string()
    {
        $lines = [
            "Received: from example.com ([0.0.0.0 helo=localhost)\r\n\tby smtp.server.com with esmtps",
            "Subject: test",
            "",
            "Hello World"
        ];

        $message = GenericMessage::fromString(\implode("\r\n", $lines));

        $this->assertEquals(
            "from example.com ([0.0.0.0 helo=localhost) by smtp.server.com with\r\n esmtps",
            (string)$message->getHeader('received')[0]->getValue()
        );
    }
}
