<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Mime;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Mime\GenericPart;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class GenericPartTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $part = new GenericPart();

        $this->assertNotSame($part, $part->withHeader(new ContentType('text/html')));
        $this->assertNotSame($part, $part->withoutHeader('content/type'));
        $this->assertNotSame($part, $part->withBody(new AsciiEncodedStream('test')));
    }

    /**
     * @test
     */
    public function it_has_case_insensitive_headers()
    {
        $part = (new GenericPart())
            ->withHeader(new ContentType('text/html'));

        $this->assertTrue($part->hasHeader('ContenT-TyPe'));
        $this->assertTrue($part->hasHeader('content-type'));
        $this->assertTrue($part->hasHeader('Content-Type'));
        $this->assertEquals('text/html; charset=UTF-8', (string)$part->getHeader('ContenT-TyPe')->getValue());
        $this->assertEquals('text/html; charset=UTF-8', (string)$part->getHeader('content-type')->getValue());
        $this->assertEquals('text/html; charset=UTF-8', (string)$part->getHeader('Content-Type')->getValue());
        $this->assertFalse($part->withoutHeader('ConTent-TYPE')->hasHeader('ContenT-TyPe'));
    }

    /**
     * @test
     */
    public function it_throws_when_receives_wrong_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new GenericPart())->withHeader(new GenericHeader('x', 'y'));
    }
}
