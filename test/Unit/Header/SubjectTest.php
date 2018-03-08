<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\Subject;

final class SubjectTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($subject, $constructed, $headerName, $headerValue)
    {
        if ($constructed) {
            $header = new Subject($subject);
            $this->assertEquals($headerName, (string)$header->getName());
            $this->assertEquals($headerValue, (string)$header->getValue());
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new Subject($subject);
        }
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['Value', true, 'Subject', 'Value'],
            ["x \n y", false, '', ''],
            ["x \r\n y", false, '', ''],
            ["x \r y", false, '', ''],
        ];
    }
}
