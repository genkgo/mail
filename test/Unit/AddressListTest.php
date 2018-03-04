<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;

final class AddressListTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_the_correct_recipient_string_with_a_single_address()
    {
        $addressList = new AddressList([
            new Address(new EmailAddress('me1@example.com'), 'name'),
        ]);

        $this->assertEquals(
            "name <me1@example.com>",
            (string)$addressList
        );
    }

    /**
     * @test
     */
    public function it_produces_the_correct_recipient_string_with_multiple_addresses()
    {
        $addressList = new AddressList([
            new Address(new EmailAddress('me1@example.com'), 'name'),
            new Address(new EmailAddress('me2@example.com'), 'name'),
            new Address(new EmailAddress('me3@example.com'), 'name'),
        ]);

        $this->assertEquals(
            "name <me1@example.com>,\r\n name <me2@example.com>,\r\n name <me3@example.com>",
            (string)$addressList
        );
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $addressList = new AddressList([
            new Address(new EmailAddress('me1@example.com'), 'name'),
        ]);

        $this->assertNotSame(
            $addressList,
            $addressList->withAddress(
            new Address(new EmailAddress('me1@example.com'), 'name')
        )
        );

        $this->assertNotSame(
            $addressList,
            $addressList->withoutAddress(
            new Address(new EmailAddress('me1@example.com'), 'name')
        )
        );
    }

    /**
     * @test
     */
    public function it_can_add_an_address()
    {
        $addressList = new AddressList([
            new Address(new EmailAddress('me1@example.com'), 'name'),
        ]);

        $this->assertEquals(
            "name <me1@example.com>,\r\n name <me2@example.com>",
            (string) $addressList->withAddress(
                new Address(new EmailAddress('me2@example.com'), 'name')
            )
        );
    }

    /**
     * @test
     */
    public function it_can_remove_an_address()
    {
        $addressList = new AddressList([
            new Address(new EmailAddress('me1@example.com'), 'name'),
        ]);

        $this->assertEquals(
            '',
            (string) $addressList->withoutAddress(
                new Address(new EmailAddress('me1@example.com'), 'name')
            )
        );
    }

    /**
     * @test
     */
    public function it_throws_when_providing_incorrect_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddressList(['']);
    }

    /**
     * @test
     */
    public function it_returns_the_first_address()
    {
        $firstAddress = new Address(new EmailAddress('x@y.com'));
        $list = new AddressList([$firstAddress]);
        $this->assertSame($firstAddress, $list->first());
    }

    /**
     * @test
     */
    public function it_throws_when_first_called_without_addresses()
    {
        $this->expectException(\OutOfRangeException::class);

        $list = new AddressList([]);
        $list->first();
    }

    /**
     * @test
     * @dataProvider provideAddressListStrings
     */
    public function it_parses_address_strings(string $addressListString, bool $constructed, int $count, string $exceptionMessage)
    {
        if ($constructed) {
            $addressList = AddressList::fromString($addressListString);
            $this->assertCount($count, $addressList);
        } else {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($exceptionMessage);
            AddressList::fromString($addressListString);
        }
    }

    /**
     * @return array
     */
    public function provideAddressListStrings()
    {
        return [
            ['', true, 0, ''],
            ['Name <local-part@domain.com>', true, 1, ''],
            ['"Name , Name" <local-part@domain.com>', true, 1, ''],
            ['"Name \" Name" <local-part@domain.com>', true, 1, ''],
            ['"Name \" Name" <"local-part"@domain.com>', true, 1, ''],
            ['local-part@domain.com', true, 1, ''],
            ['"Name <local-part@domain.com>', false, 0, 'Address uses starting quotes but no ending quotes'],
            ['X <local-part@domain.com>, Y <local-part@domain.com>', true, 2, ''],
            ['"X" <local-part@domain.com>, "Y" <local-part@domain.com>', true, 2, ''],
            ['"," <local-part@domain.com>, "Y" <local-part@domain.com>', true, 2, ''],
            ['"," <local-part@domain.com>, "Y" <"local,part"@domain.com>', true, 2, ''],
        ];
    }
}
