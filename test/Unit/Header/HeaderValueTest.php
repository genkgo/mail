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
     * @dataProvider provideValues
     */
    public function it_validates_values_and_produces_correct_output(string $value, bool $expected, string $expectedOutput)
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
    public function provideValues()
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
                "=?UTF-8?Q?Subject with not so many characters =C3=AB?="
            ],
        ];
    }

    /**
     * @test
     */
    public function it_separates_parameters_correctly()
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'))
            ->withParameter(new HeaderValueParameter('name2', 'value2'));

        $this->assertEquals('value; name1=value1; name2=value2', (string)$header);
    }

    /**
     * @test
     */
    public function it_overwrites_parameters()
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'))
            ->withParameter(new HeaderValueParameter('name1', 'value2'));

        $this->assertEquals('value; name1=value2', (string)$header);
    }

    /**
     * @test
     */
    public function it_can_prepend_a_new_line_to_a_parameter()
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'), true)
            ->withParameter(new HeaderValueParameter('name2', 'value2'));

        $this->assertEquals("value;\r\n name1=value1; name2=value2", (string)$header);
    }

    /**
     * @test
     */
    public function it_can_parse_a_string()
    {
        $header = HeaderValue::fromString('application/pdf; charset="utf-8"');

        $this->assertEquals('application/pdf; charset=utf-8', (string)$header);
        $this->assertEquals('charset=utf-8', (string)$header->getParameter('charset'));
    }

    /**
     * @test
     */
    public function it_can_parse_a_string_with_more_than_one_parameter()
    {
        $header = HeaderValue::fromString('application/pdf; charset="utf-8"; foo="bar"');

        $this->assertEquals('application/pdf; charset=utf-8; foo=bar', (string)$header);
        $this->assertEquals('charset=utf-8', (string)$header->getParameter('charset'));
        $this->assertEquals('foo=bar', (string)$header->getParameter('foo'));
    }
}
