<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\Date;

final class DateTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($date, $headerName, $headerValue)
    {
        $header = new Date(new \DateTimeImmutable($date));
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['2017-01-01 18:15:00', 'Date', 'Sun, 01 Jan 2017 18:15:00 +0000'],
        ];
    }
}
