<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentType;

final class ContentTypeTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($contentType, $charset, $headerName, $headerValue)
    {
        $header = new ContentType($contentType, $charset);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['text/html', 'UTF-8', 'Content-Type', 'text/html; charset=UTF-8'],
        ];
    }
}
