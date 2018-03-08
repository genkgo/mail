<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
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
            "Name <me@example.com>,\r\n =?UTF-8?B?VMOrc3Q=?= <me@example.com>,\r\n =?UTF-8?B?w6vDq8Orw6s=?= <me@example.com>",
            (string)$header->getValue()
        );
    }

    /**
     * @test
     */
    public function it_can_be_easier_constructed_for_single_address()
    {
        $header = To::fromSingleRecipient('me@example.com', 'Name');
        $this->assertEquals('Name <me@example.com>', (string)$header->getValue());
    }

    /**
     * @test
     */
    public function it_can_be_easier_constructed_for_multiple_addresses()
    {
        $header = To::fromArray([['bob@example.com', 'Bob'], ['john@example.com', 'John'], ['noname@example.com']]);
        $this->assertEquals("Bob <bob@example.com>,\r\n John <john@example.com>,\r\n noname@example.com", (string)$header->getValue());
    }

    /**
     * @test
     */
    public function it_cannot_be_easier_constructed_with_invalid_entry()
    {
        $this->expectException(\InvalidArgumentException::class);
        To::fromArray([['bob@example.com', 'Bob'], ['john@example.com', 'John'], ['invalid@example.com', 'Foo', 'Bar']]);
    }
}
