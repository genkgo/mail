<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\HtmlOnlyMessage;
use Genkgo\Mail\Stream\EmptyStream;
use Genkgo\Mail\Stream\OptimalTransferEncodedTextStream;

final class HtmlOnlyMessageTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_correctly_produces_message_string()
    {
        $message = (new HtmlOnlyMessage('<html><body><p>Hello World</p></body></html>'))
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')))
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            \file_get_contents(__DIR__ . '/../Stub/HtmlOnlyMessage/message.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $message = new HtmlOnlyMessage('<html><body><p>Hello World</p></body></html>');

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
        $message = (new HtmlOnlyMessage('<html><body><p>Hello World</p></body></html>'))
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
    public function it_uses_an_optimal_encoding_for_body()
    {
        $message = new HtmlOnlyMessage('<html><body><p>Hello World</p></body></html>');

        $this->assertInstanceOf(OptimalTransferEncodedTextStream::class, $message->getBody());
    }
}
