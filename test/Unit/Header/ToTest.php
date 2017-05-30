<?php

namespace Genkgo\Mail\Unit\Header;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\To;

final class ToTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_produces_correct_values()
    {
        $header = new To(
            new AddressList([
                new Address(
                    new EmailAddress('me@example.com'),
                    'Name'
                )
            ])
        );

        $this->assertEquals('To', (string)$header->getName());
        $this->assertEquals('Name <me@example.com>', (string)$header->getValue());
    }

    /**
     * @test
     */
    public function it_encodes_correctly()
    {
        $header = new To(
            new AddressList([
                new Address(
                    new EmailAddress('me@example.com'),
                    'Name'
                ),
                new Address(
                    new EmailAddress('me@example.com'),
                    'Tëst'
                ),
                new Address(
                    new EmailAddress('me@example.com'),
                    'ëëëë'
                )
            ])
        );

        $this->assertEquals(
            "Name <me@example.com>,=?UTF-8?B?VMOrc3Q=?=\r\n <me@example.com>,=?UTF-8?B?w6vDq8Orw6s=?= <me@example.com>",
            (string)$header->getValue()
        );
    }
}