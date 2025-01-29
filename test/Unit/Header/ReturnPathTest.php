<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\ReturnPath;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\EmailAddress;

final class ReturnPathTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($recipientEmail, $headerName, $headerValue): void
    {
        $header = new ReturnPath($recipientEmail);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues(): array
    {
        return [
            [new EmailAddress('me@example.com'), 'Return-Path', '<me@example.com>'],
            [null, 'Return-Path', '<>'],
        ];
    }
}
