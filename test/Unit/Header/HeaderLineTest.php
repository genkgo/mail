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
    public function it_folds_headers_when_name_plus_value_is_longer_than_78_characters(): void
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
    public function it_creates_a_line_from_a_string(): void
    {
        $line = HeaderLine::fromString('X: Y');
        $this->assertEquals('X', (string) $line->getHeader()->getName());
        $this->assertEquals('Y', (string) $line->getHeader()->getValue());
    }

    /**
     * @test
     */
    public function it_creates_a_line_from_a_encoded_string(): void
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
    public function it_creates_a_line_from_a_partial_encoded_string(): void
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
    public function it_throws_an_exception_when_there_is_no_colon(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HeaderLine::fromString('test');
    }

    /**
     * @test
     */
    public function it_parses_a_received_header(): void
    {
        $line = HeaderLine::fromString("Received: from [000.000.000.00] (helo=[000.000.00.00]) by aaaa.aaaa.aaaaaa.aaa\r\n with esmtpsa (TLSv1.3:TLS_AES_128_GCM_SHA256:128) (aaaa 0.00)\r\n (envelope-from <aaaaaaa@aaaaaa.aa>) id aaaaaa-000000-AA for\r\n aaaaaaa@aaaaaa.aa; Thu, 17 Sep 2020 09:22:46 +0000");
        $this->assertEquals(
            "from [000.000.000.00] (helo=[000.000.00.00]) by aaaa.aaaa.aaaaaa.aaa\r\n with esmtpsa (TLSv1.3:TLS_AES_128_GCM_SHA256:128) (aaaa 0.00)\r\n (envelope-from <aaaaaaa@aaaaaa.aa>) id aaaaaa-000000-AA for\r\n aaaaaaa@aaaaaa.aa; Thu, 17 Sep 2020 09:22:46 +0000",
            (string)$line->getHeader()->getValue()
        );
    }
}
