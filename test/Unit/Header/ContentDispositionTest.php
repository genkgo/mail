<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentDisposition;

final class ContentDispositionTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_produces_correct_values($method, $filename, $headerName, $headerValue)
    {
        $header = \call_user_func([ContentDisposition::class, $method], $filename);
        $this->assertEquals($headerName, (string)$header->getName());
        $this->assertEquals($headerValue, (string)$header->getValue());
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['newInline', 'inline.txt', 'Content-Disposition', 'inline; filename="inline.txt"'],
            ['newAttachment', 'attachment.txt', 'Content-Disposition', 'attachment; filename="attachment.txt"'],
            ['newAttachment', '/attachment.txt', 'Content-Disposition', 'attachment; filename="/attachment.txt"'],
        ];
    }
}
