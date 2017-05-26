<?php

namespace Genkgo\Mail\Unit;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;

final class AddressTest extends AbstractTestCase {

    /**
     * @test
     * @dataProvider provideAddresses
     */
    public function it_validates_addresses(string $email, string $name, bool $constructed, $string)
    {
        if ($constructed) {
            $address = new Address(new EmailAddress($email), $name);
            $this->assertEquals($string, (string)$address);
            $this->assertEquals($name, $address->getName());
            $this->assertEquals($email, (string)$address->getAddress());
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new Address(new EmailAddress($email), $name);
        }
    }

    /**
     * @return array
     */
    public function provideAddresses()
    {
        return [
            ['local-part@domain.com', 'Name', true, 'Name <local-part@domain.com>'],
            ['local-part@domain.com', 'Name , Name', true, '"Name , Name" <local-part@domain.com>'],
            ['local-part@domain.com', 'Name " Name', true, '"Name \" Name" <local-part@domain.com>'],
            ['local-part@domain.com', '', true, 'local-part@domain.com'],
            ['local-part@domain.com', "test\r\ntest", false, 'local-part@domain.com'],
        ];
    }

    /**
     * @test
     */
    public function it_is_equal_when_it_has_same_value()
    {
        $address = new Address(new EmailAddress('me@example.com'), 'name');
        
        $this->assertTrue(
            $address->equals(
                new Address(new EmailAddress('me@example.com'), 'name')
            )
        );

        $this->assertFalse(
            $address->equals(
                new Address(new EmailAddress('me@example.com'), 'different')
            )
        );

        $this->assertFalse(
            $address->equals(
                new Address(new EmailAddress('different@example.com'), 'name')
            )
        );

        $this->assertFalse(
            $address->equals(
                new Address(new EmailAddress('different@example.com'), 'different')
            )
        );
    }

}