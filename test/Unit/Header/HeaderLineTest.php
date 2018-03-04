<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\HeaderLine;

final class HeaderLineTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_folds_headers_when_name_plus_value_is_longer_than_78_characters()
    {
        $line = new HeaderLine(
            new GenericHeader(
                'Super-Long-Header-Value-That-Will-Make-The-Value-Exceed-Max-Length',
                'Value That Is Also Long'
            )
        );

        $this->assertEquals(
            "Super-Long-Header-Value-That-Will-Make-The-Value-Exceed-Max-Length:\r\n Value That Is Also Long",
            (string)$line
        );
    }

    /**
     * @test
     */
    public function it_creates_a_line_from_a_string()
    {
        $line = HeaderLine::fromString('X: Y');
        $this->assertEquals('X', (string) $line->getHeader()->getName());
        $this->assertEquals('Y', (string) $line->getHeader()->getValue());
    }

    /**
     * @test
     */
    public function it_creates_a_line_from_a_encoded_string()
    {
        $line = HeaderLine::fromString('X: =?UTF-8?Q?t=C3=ABst?=');
        $this->assertEquals('X', (string) $line->getHeader()->getName());
        $this->assertEquals('tëst', $line->getHeader()->getValue()->getRaw());

        $line = HeaderLine::fromString('X: =?UTF-8?B?dMOrc3Q=?=');
        $this->assertEquals('X', (string) $line->getHeader()->getName());
        $this->assertEquals('tëst', $line->getHeader()->getValue()->getRaw());
    }

    /**
     * @test
     */
    public function it_creates_a_line_from_a_partial_encoded_string()
    {
        $line = HeaderLine::fromString('X: =?UTF-8?Q?t=C3=ABst?= <local-part@domain.com>');
        $this->assertEquals('X', (string) $line->getHeader()->getName());
        $this->assertEquals('=?UTF-8?Q?t=C3=ABst?= <local-part@domain.com>', $line->getHeader()->getValue()->getRaw());

        $line = HeaderLine::fromString('X: =?UTF-8?B?bMOkc3QgbmFtZSwgZsOvcnN0IG5hbWU=?= <local-part@domain.com>');
        $this->assertEquals('X', (string) $line->getHeader()->getName());
        $this->assertEquals('=?UTF-8?B?bMOkc3QgbmFtZSwgZsOvcnN0IG5hbWU=?= <local-part@domain.com>', $line->getHeader()->getValue()->getRaw());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_there_is_no_colon()
    {
        $this->expectException(\InvalidArgumentException::class);
        HeaderLine::fromString('test');
    }
}
