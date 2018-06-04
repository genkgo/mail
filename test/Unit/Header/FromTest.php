<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\From;

final class FromTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($recipientEmail, $recipientName, $headerName, $headerValue)
    {
        $header = new From(new Address(new EmailAddress($recipientEmail), $recipientName));
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['me@example.com', 'Name', 'From', 'Name <me@example.com>'],
        ];
    }

    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_can_be_easier_constructed_with_email_address_and_name()
    {
        $header = From::fromAddress('me@example.com', 'Name');
        $this->assertEquals('Name <me@example.com>', (string)$header->getValue());
    }

    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_can_be_easier_constructed_with_email_address()
    {
        $header = From::fromEmailAddress('me@example.com');
        $this->assertEquals('me@example.com', (string)$header->getValue());
    }

    /**
     * @test
     */
    public function it_uses_correct_encoding_with_long_names()
    {
        $from = new From(
            new Address(
                new EmailAddress('webmaster@xyz.domain.com'),
                'Webmaster Organization Name With a Very Long Name Somewhere-Somehwere'
            )
        );

        $this->assertEquals(
            "Webmaster Organization Name With a Very Long Name\r\n Somewhere-Somehwere <webmaster@xyz.domain.com>",
            (string)$from->getValue()
        );
    }
}
