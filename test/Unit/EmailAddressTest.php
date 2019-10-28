<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\EmailAddress;

final class EmailAddressTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideAddresses
     */
    public function it_validates_addresses(string $address, bool $expected, $localPart, $domain)
    {
        if ($expected) {
            $address = new EmailAddress($address);
            $this->assertEquals($address, $address->getAddress());
            $this->assertEquals($localPart, $address->getLocalPart());
            $this->assertEquals($domain, $address->getDomain());
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
            ['local-part@domain.com', true, 'local-part', 'domain.com'],
            ['local-part@münchen.com', true, 'local-part', 'münchen.com'],
            ['münchen@münchen.com', true, 'münchen', 'münchen.com'],
            ['local-part@[IPv6:2001:db8:1ff::a0b:dbd0]', true, 'local-part', '[IPv6:2001:db8:1ff::a0b:dbd0]'],
            ['local-part+symbol@domain.com', true, 'local-part+symbol', 'domain.com'],
            ['"local-part with-space"@domain.com', true, '"local-part with-space"', 'domain.com'],
            ['local-part@top-level-domain', true, 'local-part', 'top-level-domain'],
            ['missing-at.domain.com', false, '', ''],
            ['multiple@local-parts@domain.com', false, '', ''],
            ['multiple@local@parts@domain.com', false, '', ''],
            ['@example.com', false, '', ''],
            ['missing-domain@', false, '', ''],
            ["x\ry@z", false, '', ''],
            ["x\r\ny@z", false, '', ''],
            ["x\ny@z", false, '', ''],
        ];
    }

    /**
     * @test
     */
    public function it_can_be_constructed_with_a_multi_byte_name_leading_to_new_line_in_single_byte()
    {
        $address = new EmailAddress('Миха@domain.com');
        $this->assertEquals('Миха@domain.com', (string)$address);
    }

    /**
     * @test
     */
    public function it_is_equal_when_it_has_same_value()
    {
        $address = new EmailAddress('me@example.com');

        $this->assertTrue(
            $address->equals(
                new EmailAddress('me@example.com')
            )
        );

        $this->assertFalse(
            $address->equals(
                new EmailAddress('different@example.com')
            )
        );
    }

    /**
     * @test
     */
    public function it_puny_codes_local_part_and_domain()
    {
        $address = new EmailAddress('münchen@münchen.com');

        $this->assertEquals('xn--mnchen-3ya@xn--mnchen-3ya.com', $address->getPunyCode());
    }
}
