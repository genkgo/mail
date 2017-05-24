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
    public function validate_addresses(string $address, string $name, bool $constructed, $string)
    {
        if ($constructed) {
            $address = new Address(new EmailAddress($address), $name);
            $this->assertEquals($string, (string)$address);
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new EmailAddress($address);
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
        ];
    }

}