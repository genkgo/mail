<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\MessageId;
use Genkgo\TestMail\AbstractTestCase;

final class MessageIdTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($left, $right, $headerName, $headerValue)
    {
        $header = new MessageId($left, $right);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['left', 'right', 'Message-ID', '<left@right>'],
            ['left', 'münchen.com', 'Message-ID', '<left@xn--mnchen-3ya.com>'],
        ];
    }
}
