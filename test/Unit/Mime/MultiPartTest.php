<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Mime;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Mime\MultiPartInterface;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Mime\Boundary;
use Genkgo\Mail\Mime\GenericPart;
use Genkgo\Mail\Mime\MultiPart;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class MultiPartTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $part = new MultiPart(new Boundary('test'), new ContentType('multipart/mixed'));

        $this->assertNotSame($part, $part->withPart(new GenericPart()));
        $this->assertNotSame($part, $part->withParts([new GenericPart()]));
    }

    /**
     * @test
     */
    public function it_cannot_add_headers()
    {
        $this->expectException(\RuntimeException::class);

        $part = new MultiPart(new Boundary('test'), new ContentType('multipart/mixed'));
        $part->withHeader(new GenericHeader('x', 'y'));
    }

    /**
     * @test
     */
    public function it_cannot_remove_headers()
    {
        $this->expectException(\RuntimeException::class);

        $part = new MultiPart(new Boundary('test'), new ContentType('multipart/mixed'));
        $part->withoutHeader('x');
    }

    /**
     * @test
     */
    public function it_cannot_modify_body()
    {
        $this->expectException(\RuntimeException::class);

        $part = new MultiPart(new Boundary('test'), new ContentType('multipart/mixed'));
        $part->withBody(new AsciiEncodedStream('body'));
    }

    /**
     * @test
     */
    public function it_throws_when_receives_wrong_content_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        new MultiPart(new Boundary('test'), new ContentType('text/plain'));
    }

    /**
     * @test
     */
    public function it_has_header_content_type()
    {
        $part = new MultiPart(new Boundary('test'), new ContentType('multipart/mixed'));

        $this->assertTrue($part->hasHeader('content-type'));
        $this->assertEquals(
            'multipart/mixed; boundary=test',
            (string)$part->getHeader('content-type')->getValue()
        );
        $this->assertCount(1, $part->getHeaders());
    }

    /**
     * @test
     */
    public function it_can_parse_multi_layer_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../../Stub/MessageBodyCollection/full-formatted-message.eml')
        );
        $multiPart = MultiPart::fromMessage($message);

        $this->assertCount(2, $multiPart->getParts());
        $this->assertSame('GenkgoMailV2Part6d41f369b545', (string)$multiPart->getBoundary());
        $this->assertSame(
            'multipart/mixed; boundary=GenkgoMailV2Part6d41f369b545',
            (string)$multiPart->getHeader('Content-Type')->getValue()
        );

        /** @var MultiPartInterface $part */
        foreach ($multiPart->getParts() as $index => $part) {
            switch ($index) {
                case 0:
                    $this->assertInstanceOf(MultiPart::class, $part);
                    $this->assertCount(2, $part->getParts());
                    $this->assertSame(
                        'multipart/related; boundary=GenkgoMailV2Partecbe5baa2673',
                        (string)$part->getHeader('Content-Type')->getValue()
                    );
                    break;
                case 1:
                    $this->assertInstanceOf(GenericPart::class, $part);
                    $this->assertSame(
                        'text/plain; charset=UTF-8',
                        (string)$part->getHeader('Content-Type')->getValue()
                    );
                    break;
            }
        }
    }

    /**
     * @test
     */
    public function it_can_parse_layered_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../../Stub/MessageBodyCollection/html-and-text.eml')
        );

        $multiPart = MultiPart::fromMessage($message);

        $this->assertCount(2, $multiPart->getParts());
        $this->assertSame('GenkgoMailV2Part187e28bf3cb4', (string)$multiPart->getBoundary());
        $this->assertSame(
            'multipart/alternative; boundary=GenkgoMailV2Part187e28bf3cb4',
            (string)$multiPart->getHeader('Content-Type')->getValue()
        );
    }
}
