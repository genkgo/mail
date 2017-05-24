<?php

namespace Genkgo\Mail\Unit\Header;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\To;

final class ToTest extends AbstractTestCase
{

    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($recipientEmail, $recipientName, $headerName, $headerValue)
    {
        $header = new To([new Address(new EmailAddress($recipientEmail), $recipientName)]);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['me@example.com', 'Name', 'To', 'Name <me@example.com>'],
        ];
    }

    /**
     * @test
     */
    public function it_produces_correct_values_with_many_address()
    {
        $header = new To([
            new Address(new EmailAddress('me1@example.com'), 'name'),
            new Address(new EmailAddress('me2@example.com'), 'name'),
            new Address(new EmailAddress('me3@example.com'), 'name'),
            new Address(new EmailAddress('me4@example.com'), 'name'),
            new Address(new EmailAddress('me5@example.com'), 'name'),
            new Address(new EmailAddress('me6@example.com'), 'name'),
            new Address(new EmailAddress('me7@example.com'), 'name'),
            new Address(new EmailAddress('me8@example.com'), 'name'),
        ]);

        $this->assertEquals(
            "name <me1@example.com>,name <me2@example.com>,name <me3@example.com>,name\r\n <me4@example.com>,name <me5@example.com>,name <me6@example.com>,name\r\n <me7@example.com>,name <me8@example.com>",
            (string)$header->getValue()
        );
    }
}