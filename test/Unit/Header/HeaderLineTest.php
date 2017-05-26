<?php
declare(strict_types=1);

namespace Genkgo\Mail\Unit\Header;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\HeaderLine;

final class HeaderLineTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_folds_headers_when_name_plus_value_is_longer_than_78_characters() {
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
}