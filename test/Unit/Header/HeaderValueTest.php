<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\HeaderValue;
use Genkgo\Mail\Header\HeaderValueParameter;

final class HeaderValueTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideEncodeValues
     */
    public function it_validates_values_and_encodes_correctly(string $value, bool $expected, string $expectedOutput): void
    {
        if ($expected) {
            $header = new HeaderValue($value);
            $this->assertEquals($expectedOutput, (string)$header);
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new HeaderValue($value);
        }
    }

    /**
     * @return array
     */
    public function provideEncodeValues(): array
    {
        return [
            ['Ascii', true, 'Ascii'],
            ["With\nNew-Line", false, ''],
            ["With\r\nNew-Line", false, ''],
            ["With\rNew-Line", false, ''],
            [
                'Value Value Value Value Value Value Value Value Value Value Value Value Value Value',
                true,
                "Value Value Value Value Value Value Value Value Value Value Value\r\n Value Value Value"
            ],
            [
                'Subject with special characters ëëëëëëëëëëëëëëë Value Value Value ',
                true,
                "=?UTF-8?B?U3ViamVjdCB3aXRoIHNwZWNpYWwgY2hhcmFjdGVycyDDq8Orw6vDq8Orw6vDq8Orw6vD\r\n q8Orw6vDq8Orw6sgVmFsdWUgVmFsdWUgVmFsdWUg?="
            ],
            [
                'Subject with not so many characters ë',
                true,
                "=?UTF-8?Q?Subject_with_not_so_many_characters_=C3=AB?="
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideParseValues
     */
    public function it_parses_values_correctly(string $value, bool $expected, string $expectedOutput): void
    {
        if ($expected) {
            $header = HeaderValue::parse($value);
            $this->assertEquals($expectedOutput, $header->getRaw());
        } else {
            $this->expectException(\InvalidArgumentException::class);
            HeaderValue::parse($value);
        }
    }

    /**
     * @return array
     */
    public function provideParseValues(): array
    {
        return [
            [
                "=?UTF-8?Q?Subject_with_not_so_many_characters_=C3=AB?=",
                true,
                'Subject with not so many characters ë'
            ],
            [
                "=?UTF-8?Q?Structureren_van_Genkgo=E2=80=91pagina=E2=80=99s_voor_betere_AI=E2?= =?UTF-8?Q?=80=91leesbaarheid?=",
                true,
                'Structureren van Genkgo‑pagina’s voor betere AI‑leesbaarheid'
            ],
            [
                "=?UTF-8?B?U3ViamVjdCB3aXRoIHNwZWNpYWwgY2hhcmFjdGVycyDDq8Orw6vDq8Orw6vDq8Orw6vD\r\n q8Orw6vDq8Orw6sgVmFsdWUgVmFsdWUgVmFsdWUg?=",
                true,
                'Subject with special characters ëëëëëëëëëëëëëëë Value Value Value'
            ],
            [
                "=?UTF-8?Q?Structureren_van_Genkgo=E2=80=91pagina=E2=80=99s_voor_betere_AI=E2?= =?UTF-8?Q?=80=91leesbaarheid",
                true,
                'Structureren van Genkgo‑pagina’s voor betere AI‑leesbaarheid'
            ],
            [
                "=?UTF-8?B?U3ViamVjdCB3aXRoIHNwZWNpYWwgY2hhcmFjdGVycyDDq8Orw6vDq8Orw6vDq8Orw6vD\r\n q8O",
                true,
                "Subject with special characters \u{00EB}\u{00EB}\u{00EB}\u{00EB}\u{00EB}\u{00EB}\u{00EB}\u{00EB}\u{00EB}\u{00EB}". \chr(195)
            ],
        ];
    }

    /**
     * @test
     */
    public function it_separates_parameters_correctly(): void
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'))
            ->withParameter(new HeaderValueParameter('name2', 'value2'));

        $this->assertEquals('value; name1=value1; name2=value2', (string)$header);
    }

    /**
     * @test
     */
    public function it_overwrites_parameters(): void
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'))
            ->withParameter(new HeaderValueParameter('name1', 'value2'));

        $this->assertEquals('value; name1=value2', (string)$header);
    }

    /**
     * @test
     */
    public function it_can_prepend_a_new_line_to_a_parameter(): void
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'), true)
            ->withParameter(new HeaderValueParameter('name2', 'value2'));

        $this->assertEquals("value;\r\n name1=value1; name2=value2", (string)$header);
    }

    /**
     * @test
     */
    public function it_can_parse_a_string(): void
    {
        $header = HeaderValue::fromString('application/pdf; charset="utf-8"');

        $this->assertEquals('application/pdf; charset=utf-8', (string)$header);
        $this->assertEquals('charset=utf-8', (string)$header->getParameter('charset'));
    }

    /**
     * @test
     */
    public function it_can_parse_a_string_with_more_than_one_parameter(): void
    {
        $header = HeaderValue::fromString('application/pdf; charset="utf-8"; foo="bar"');

        $this->assertEquals('application/pdf; charset=utf-8; foo=bar', (string)$header);
        $this->assertEquals('charset=utf-8', (string)$header->getParameter('charset'));
        $this->assertEquals('foo=bar', (string)$header->getParameter('foo'));
    }

    /**
     * @test
     */
    public function it_can_parse_rfc2047_encoded_parameters(): void
    {
        $header = HeaderValue::parse('attachment; filename="=?UTF-8?Q?Scherm=C2=ADafbeelding_2026=2D10=2D22_om_16=2E00=2E25=2Epng?="');

        $this->assertEquals('attachment; filename*="UTF-8\'\'Scherm%C2%ADafbeelding%202026-10-22%20om%2016.00.25.png"', (string)$header);
        $this->assertEquals('Scherm­afbeelding 2026-10-22 om 16.00.25.png', $header->getParameter('filename')->getValue());
    }

    /**
     * @test
     */
    public function it_can_parse_rfc2231_encoded_parameters(): void
    {
        $header = HeaderValue::parse('attachment; filename*="UTF-8\'\'Scherm%C2%ADafbeelding%202026-10-22%20om%2016.00.25.png"');

        $this->assertEquals('attachment; filename*="UTF-8\'\'Scherm%C2%ADafbeelding%202026-10-22%20om%2016.00.25.png"', (string)$header);
        $this->assertEquals('Scherm­afbeelding 2026-10-22 om 16.00.25.png', $header->getParameter('filename')->getValue());
    }

    /**
     * @test
     */
    public function it_can_parse_a_string_with_more_than_one_line(): void
    {
        $header1 = HeaderValue::fromString("Name \"Quoted Name\"\r\n <name@domain.com>");
        $header2 = HeaderValue::fromString("Name \"Quoted Name\"\r\n  <name@domain.com>");

        $this->assertEquals('Name "Quoted Name" <name@domain.com>', $header1->getRaw());
        $this->assertEquals('Name "Quoted Name"  <name@domain.com>', $header2->getRaw());
    }

    /**
     * @test
     */
    public function it_can_add_extended_parameters(): void
    {
        $header = (new HeaderValue('value'))
            ->withAsciiCompatibleExtendedParameter(
                new HeaderValueParameter(
                    'filename',
                    'Scherm­afbeelding 2026-10-22 om 16.00.25.png'
                )
            );

        $this->assertEquals(
            "value;\r\n filename=\"Scherm_afbeelding 2026-10-22 om 16.00.25.png\";\r\n filename*=\"UTF-8''Scherm%C2%ADafbeelding%202026-10-22%20om%2016.00.25.png\"",
            (string)$header
        );
    }
}
