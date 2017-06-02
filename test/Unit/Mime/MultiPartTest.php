<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Mime;

use Genkgo\TestMail\AbstractTestCase;;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Mime\Boundary;
use Genkgo\Mail\Mime\GenericPart;
use Genkgo\Mail\Mime\MultiPart;
use Genkgo\Mail\Stream\BitEncodedStream;

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
        $part->withBody(new BitEncodedStream('body'));
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
            'multipart/mixed; boundary="test"',
            (string)$part->getHeader('content-type')->getValue()
        );
        $this->assertArrayHasKey('content-type', $part->getHeaders());
    }
}