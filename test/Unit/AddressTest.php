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

    /**
     * @test
     * @dataProvider provideAddressStrings
     */
    public function it_parses_address_strings(string $addressString, bool $constructed, string $email, string $name)
    {
        if ($constructed) {
            $address = Address::fromString($addressString);
            $this->assertEquals($name, $address->getName());
            $this->assertEquals($email, (string)$address->getAddress());
        } else {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($email);
            Address::fromString($addressString);
        }
    }

    /**
     * @return array
     */
    public function provideAddressStrings()
    {
        return [
            ['Name <local-part@domain.com>', true, 'local-part@domain.com', 'Name'],
            ['"Name , Name" <local-part@domain.com>', true, 'local-part@domain.com', 'Name , Name', ],
            ['"Name \" Name" <local-part@domain.com>', true, 'local-part@domain.com', 'Name " Name'],
            ['local-part@domain.com', true, 'local-part@domain.com', ''],
            ['"Name <local-part@domain.com>', false, 'Address uses starting quotes but no ending quotes', ''],
            ['"Name" <local-part@domain.com', false, 'Address uses starting tag (<) but no ending tag (>)', ''],
            ['"Name" <local-part@domain.com>e', false, 'Invalid characters used after <>', ''],
            ['e"Name" <local-part@domain.com>', false, 'Invalid characters before "', ''],
            ['"Name" <"local-part"@domain.com>', true, '"local-part"@domain.com', 'Name'],
            ['', false, 'Address cannot be empty', ''],
        ];
    }
}