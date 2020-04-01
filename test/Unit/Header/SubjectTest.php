<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\Subject;

final class SubjectTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($subject, $constructed, $headerName, $headerValue): void
    {
        if ($constructed) {
            $header = new Subject($subject);
            $this->assertEquals($headerName, (string)$header->getName());
            $this->assertEquals($headerValue, (string)$header->getValue());
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new Subject($subject);
        }
    }

    /**
     * @test
     */
    public function it_can_be_constructed_with_a_multi_byte_name_leading_to_new_line_in_single_byte(): void
    {
        $subject = new Subject('Миха');
        $this->assertEquals('=?UTF-8?B?0JzQuNGF0LA=?=', (string)$subject->getValue());
    }

    /**
     * @test
     */
    public function it_does_not_add_a_space_in_subject(): void
    {
        $subject = new Subject('AAAAAAAAAAA - Aaaaaa aaa aaaaaaa: “Aaaa Aaaaaaaaa aaaaa aaaa aaaaaa aaaaaaaaa”');
        $this->assertEquals(
            "=?UTF-8?Q?AAAAAAAAAAA - Aaaaaa aaa aaaaaaa: =E2=80=9CAaaa Aaaaaaaaa aaaaa aaa=?=\r\n =?UTF-8?Q?a aaaaaa aaaaaaaaa=E2=80=9D?=",
            (string)$subject->getValue()
        );
    }

    /**
     * @return array
     */
    public function provideValues(): array
    {
        return [
            ['Value', true, 'Subject', 'Value'],
            ["x \n y", false, '', ''],
            ["x \r\n y", false, '', ''],
            ["x \r y", false, '', ''],
        ];
    }
}
