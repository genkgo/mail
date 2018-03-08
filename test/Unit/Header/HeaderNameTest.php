<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\HeaderName;

final class HeaderNameTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideNames
     */
    public function validate_name(string $name, bool $expected)
    {
        if ($expected) {
            $this->assertInstanceOf(HeaderName::class, new HeaderName($name));
        } else {
            $this->expectException(\InvalidArgumentException::class);
            new HeaderName($name);
        }
    }

    /**
     * @return array
     */
    public function provideNames()
    {
        return [
            ['Subject', true],
            ['X-Longer', true],
            ['X-With Space', false],
            ["X-With\nNew-Line", false],
            ["X-With\r\nNew-Line", false],
            ["X-With\rNew-Line", false],
            ["X-With:Colon", false],
            ["X-With-Special-Character-ë", false],
        ];
    }
}
