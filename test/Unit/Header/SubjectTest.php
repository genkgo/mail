<?php

namespace Genkgo\Mail\Unit\Header;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Header\Subject;

final class SubjectTest extends AbstractTestCase
{

    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($subject, $headerName, $headerValue)
    {
        $header = new Subject($subject);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['Value', 'Subject', 'Value'],
        ];
    }


}