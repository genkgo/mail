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
    public function it_casts_to_string(): void
    {
        $list = new SectionList(['HEADER']);
        $this->assertSame('[HEADER]', (string)$list);
    }

    /**
     * @test
     */
    public function it_accepts_header_text(): void
    {
        $list = new SectionList(['HEADER', 'TEXT']);
        $this->assertSame('[HEADER TEXT]', (string)$list);
    }

    /**
     * @test
     */
    public function it_accepts_header_fields_with_list(): void
    {
        $list = new SectionList(['HEADER.FIELDS (Subject)']);
        $this->assertSame('[HEADER.FIELDS (Subject)]', (string)$list);
    }

    /**
     * @test
     */
    public function it_accepts_header_fields_not_with_list(): void
    {
        $list = new SectionList(['HEADER.FIELDS (Subject)']);
        $this->assertSame('[HEADER.FIELDS (Subject)]', (string)$list);
    }

    /**
     * @test
     */
    public function it_throws_header_fields_without_list(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['HEADER.FIELDS']);
    }

    /**
     * @test
     */
    public function it_throws_header_fields_not_without_list(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['HEADER.FIELDS.NOT']);
    }

    /**
     * @test
     */
    public function it_uses_brackets_empty_list(): void
    {
        $list = new SectionList();
        $this->assertSame('[]', (string)$list);
    }

    /**
     * @test
     */
    public function it_parses_string(): void
    {
        $list = SectionList::fromString('[HEADER.FIELDS (Subject)]');
        $this->assertSame('[HEADER.FIELDS (Subject)]', (string)$list);

        $list = SectionList::fromString('[HEADER.FIELDS (Subject Date)]');
        $this->assertSame('[HEADER.FIELDS (Subject Date)]', (string)$list);
    }

    /**
     * @test
     */
    public function it_throws_when_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['']);
    }

    /**
     * @test
     */
    public function it_throws_when_header_fields_not_followed_by_list(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SectionList(['HEADER.FIELDS']);
    }

    /**
     * @test
     */
    public function it_throws_when_parsing_header_fields_not_followed_by_list(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SectionList::fromString('[HEADER.FIELDS HEADER]');
    }

    /**
     * @test
     */
    public function it_throws_when_parsing_header_fields_not_followed_by_anything(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SectionList::fromString('[HEADER.FIELDS]');
    }

    /**
     * @test
     */
    public function it_throws_when_parsing_invalid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SectionList::fromString('{HEADER}');
    }
}
