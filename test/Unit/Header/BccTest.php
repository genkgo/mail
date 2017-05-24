<?php

namespace Genkgo\Mail\Unit\Header;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Bcc;

final class BccTest extends AbstractTestCase
{

    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($recipientEmail, $recipientName, $headerName, $headerValue)
    {
        $header = new Bcc([new Address(new EmailAddress($recipientEmail), $recipientName)]);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['me@example.com', 'Name', 'Bcc', 'Name <me@example.com>'],
        ];
    }


}