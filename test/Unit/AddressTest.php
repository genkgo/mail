<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;

final class AddressTest extends AbstractTestCase
{
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
            ['local-part@domain.com', "tëst", true, '=?UTF-8?B?dMOrc3Q=?= <local-part@domain.com>'],
            ['local-part@münchen.com', 'Name', true, 'Name <local-part@xn--mnchen-3ya.com>'],
            ['münchen@münchen.com', 'Name', true, 'Name <xn--mnchen-3ya@xn--mnchen-3ya.com>'],
            ['a."local-part"@domain.com', "test", true, 'test <a."local-part"@domain.com>'],
            ['h.sprode@domain.com', "sprode, henriëtte", true, '=?UTF-8?B?c3Byb2RlLCBoZW5yacOrdHRl?= <h.sprode@domain.com>'],
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
     */
    public function it_fails_when_it_contains_bad_characters()
    {
        $this->expectException(\InvalidArgumentException::class);

        Address::fromString(
            (string)(new Address(new EmailAddress('test@test.com'), 'MariÃ?Â«lla Test'))
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
     * @test
     */
    public function it_can_be_converted_to_readable_string()
    {
        $address = new Address(new EmailAddress('local-part@münchen.com'), 'sprode, henriëtte');
        $this->assertEquals('"sprode, henriëtte" <local-part@münchen.com>', $address->toReadableString());

        $address = new Address(new EmailAddress('local-part@münchen.com'), 'Frederik');
        $this->assertEquals('Frederik <local-part@münchen.com>', $address->toReadableString());

        $address = new Address(new EmailAddress('local-part@münchen.com'));
        $this->assertEquals('local-part@münchen.com', $address->toReadableString());
    }

    /**
     * @test
     */
    public function it_can_be_constructed_with_a_multi_byte_name_leading_to_new_line_in_single_byte()
    {
        $address = new Address(new EmailAddress('local@domain.com'), 'Миха');
        $this->assertEquals('=?UTF-8?B?0JzQuNGF0LA=?= <local@domain.com>', (string)$address);
    }

    /**
     * @test
     */
    public function it_quotes_folded_addresses_with_quotes_near_the_end()
    {
        $address = new Address(
            new EmailAddress('address@domain.com'),
            'Long 7bit name with only normal characters but with quoted characters near the "end"'
        );

        $this->assertEquals(
            "\"Long 7bit name with only normal characters but with quoted\r\n characters near the \\\"end\\\"\" <address@domain.com>",
            (string)$address
        );
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
            ['<local-part@domain.com>', true, 'local-part@domain.com', ''],
            ['"Name <local-part@domain.com>', false, 'Address uses starting quotes but no ending quotes', ''],
            ['"Name" <local-part@domain.com', false, 'Address uses starting tag (<) but no ending tag (>)', ''],
            ['"Name" <local-part@domain.com>e', false, 'Invalid characters used after <>', ''],
            ['e"Name" <local-part@domain.com>', false, 'Invalid characters before "', ''],
            ['"Name" <"local-part"@domain.com>', true, '"local-part"@domain.com', 'Name'],
            ['"Name" <a."local-part"@domain.com>', true, 'a."local-part"@domain.com', 'Name'],
            ['a."local-part"@domain.com', true, 'a."local-part"@domain.com', ''],
            ['\'a."\'\ -OQueueDirectory=\%0D<?=eval($_GET[c])?>\ -X/var/www/html/"@a.php', true, '\'a."\' -OQueueDirectory=%0D<?=eval($_GET[c])?> -X/var/www/html/"@a.php', ''],
            ['', false, 'Address cannot be empty', ''],
            ['=?UTF-8?Q?t=C3=ABst?= <local-part@domain.com>', true, 'local-part@domain.com', 'tëst'],
            ['=?UTF-8?B?dMOrc3Q=?= <local-part@domain.com>', true, 'local-part@domain.com', 'tëst'],
            ['=?UTF-8?B?bMOkc3QgbmFtZSwgZsOvcnN0IG5hbWU=?= <local-part@domain.com>', true, 'local-part@domain.com', 'läst name, fïrst name'],
        ];
    }
}
