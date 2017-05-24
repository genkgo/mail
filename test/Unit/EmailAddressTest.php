<?php

namespace Genkgo\Mail\Unit;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\EmailAddress;

final class EmailAddressTest extends AbstractTestCase {

    /**
     * @test
     * @dataProvider provideAddresses
     */
    public function validate_addresses(string $address, bool $expected)
    {
        if ($expected) {
            $address = new EmailAddress($address);
            $this->assertEquals($address, $address->getAddress());
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
            ['local-part@domain.com', true],
            ['local-part@münchen.com', true],
            ['local-part@[IPv6:2001:db8:1ff::a0b:dbd0]', true],
            ['local-part+symbol@example.com', true],
            ['"local-part with-space"@example.com', true],
            ['local-part@top-level-domain', true],
            ['missing-at.domain.com', false],
            ['multiple@local-parts@domain.com', false],
            ['multiple@local@parts@domain.com', false],
            ['@example.com', false],
            ['missing-domain@', false],
        ];
    }

}