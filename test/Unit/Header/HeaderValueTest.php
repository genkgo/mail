<?php

namespace Genkgo\Mail\Unit\Header;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Header\HeaderValue;
use Genkgo\Mail\Header\HeaderValueParameter;

final class HeaderValueTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideNames
     */
    public function it_validates_values_and_produces_correct_output(string $name, bool $expected, string $expectedOutput)
    {
        if ($expected) {
            $header = new HeaderValue($name);
            $this->assertEquals($expectedOutput, (string)$header);
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new HeaderValue($name);
        }
    }

    /**
     * @return array
     */
    public function provideNames()
    {
        return [
            ['Ascii', true, 'Ascii'],
            ["X-With\nNew-Line", false, ''],
            ["X-With\r\nNew-Line", false, ''],
            ["X-With\rNew-Line", false, ''],
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

        $this->assertEquals('value; name1="value1"; name2="value2"', (string)$header);
    }

    /**
     * @test
     */
    public function it_overwrites_parameters()
    {
        $header = (new HeaderValue('value'))
            ->withParameter(new HeaderValueParameter('name1', 'value1'))
            ->withParameter(new HeaderValueParameter('name1', 'value2'));

        $this->assertEquals('value; name1="value2"', (string)$header);
    }
}