<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData;

use Genkgo\Mail\Protocol\Imap\MessageData\SectionList;
use Genkgo\TestMail\AbstractTestCase;

final class SectionListTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $list = new SectionList(['HEADER']);
        $this->assertSame('[HEADER]', (string)$list);
    }

    /**
     * @test
     */
    public function it_accepts_header_text()
    {
        $list = new SectionList(['HEADER', 'TEXT']);
        $this->assertSame('[HEADER TEXT]', (string)$list);
    }

    /**
     * @test
     */
    public function it_accepts_header_fields_with_list()
    {
        $list = new SectionList(['HEADER.FIELDS (Subject)']);
        $this->assertSame('[HEADER.FIELDS (Subject)]', (string)$list);
    }

    /**
     * @test
     */
    public function it_accepts_header_fields_not_with_list()
    {
        $list = new SectionList(['HEADER.FIELDS (Subject)']);
        $this->assertSame('[HEADER.FIELDS (Subject)]', (string)$list);
    }

    /**
     * @test
     */
    public function it_throws_header_fields_without_list()
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['HEADER.FIELDS']);
    }

    /**
     * @test
     */
    public function it_throws_header_fields_not_without_list()
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['HEADER.FIELDS.NOT']);
    }

    /**
     * @test
     */
    public function it_uses_brackets_empty_list()
    {
        $list = new SectionList();
        $this->assertSame('[]', (string)$list);
    }

    /**
     * @test
     */
    public function it_parses_string()
    {
        $list = SectionList::fromString('[HEADER.FIELDS (Subject)]');
        $this->assertSame('[HEADER.FIELDS (Subject)]', (string)$list);

        $list = SectionList::fromString('[HEADER.FIELDS (Subject Date)]');
        $this->assertSame('[HEADER.FIELDS (Subject Date)]', (string)$list);
    }

    /**
     * @test
     */
    public function it_throws_when_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['']);
    }

    /**
     * @test
     */
    public function it_throws_when_header_fields_not_followed_by_list()
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['HEADER.FIELDS']);
    }

    /**
     * @test
     */
    public function it_throws_when_parsing_header_fields_not_followed_by_list()
    {
        $this->expectException(\InvalidArgumentException::class);
        SectionList::fromString('[HEADER.FIELDS HEADER]');
    }

    /**
     * @test
     */
    public function it_throws_when_parsing_header_fields_not_followed_by_anything()
    {
        $this->expectException(\InvalidArgumentException::class);
        SectionList::fromString('[HEADER.FIELDS]');
    }

    /**
     * @test
     */
    public function it_throws_when_parsing_invalid_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        SectionList::fromString('{HEADER}');
    }
}
