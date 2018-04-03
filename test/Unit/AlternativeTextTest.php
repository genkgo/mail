<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\Mail\AlternativeText;
use Genkgo\TestMail\AbstractTestCase;

final class AlternativeTextTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider provideHtmlFiles
     */
    public function it_converts_html_to_plain_text($htmlFile, $txtFile)
    {
        $html = \file_get_contents(__DIR__ . '/../Stub/AlternativeText/' . $htmlFile);
        $text = \file_get_contents(__DIR__ . '/../Stub/AlternativeText/' . $txtFile);

        $alternativeText = AlternativeText::fromHtml($html);
        $this->assertEquals($text, (string) $alternativeText);
    }

    /**
     * @test
     */
    public function it_strips_tags_on_dom_exception()
    {
        $html = '';
        $text = '';

        $alternativeText = AlternativeText::fromHtml($html);
        $this->assertEquals($text, (string) $alternativeText);
    }

    /**
     * @return array
     */
    public function provideHtmlFiles()
    {
        return [
            ['simple.html', 'simple.crlf.txt'],
            ['error.html', 'error.txt'],
        ];
    }
}
