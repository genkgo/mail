<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\OptimalTransferEncodedTextStream;

final class OptimalTransferEncodedTextStreamTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideText
     */
    public function it_uses_correct_transfer_encoding($text, $expectedEncoding)
    {
        $stream = new OptimalTransferEncodedTextStream($text);

        $this->assertEquals(['transfer-encoding' => $expectedEncoding], $stream->getMetadata(['transfer-encoding']));
    }

    /**
     * @return array
     */
    public function provideText()
    {
        return [
            [\str_repeat('test1 test2', 50), '7bit'],
            [\str_repeat('tëst, test2', 50), 'quoted-printable'],
            [\str_repeat('tëst1 test2', 50), 'quoted-printable'],
            [\str_repeat('ëëëëë ëëëëë', 50), 'base64'],
            ["\x00", 'base64'],
            ["\x80", 'base64'],
            ["\u{aa}", 'base64'],
            ["\u{1F600}", 'base64'],
        ];
    }
}
