<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class TagTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_cast_to_string()
    {
        $this->assertSame('TAG1', (string)new Tag('TAG1'));
    }

    /**
     * @test
     */
    public function it_can_be_created_from_a_nonce()
    {
        $this->assertSame('TAG1', (string)Tag::fromNonce(1));
    }

    /**
     * @test
     */
    public function it_can_extract_body_from_a_line_starting_with_tag()
    {
        $tag = Tag::fromNonce(1);
        $this->assertSame('hello world', $tag->extractBodyFromLine('TAG1 hello world'));
    }

    /**
     * @test
     */
    public function it_throws_when_line_does_not_start_with_tag()
    {
        $this->expectException(\InvalidArgumentException::class);
        Tag::fromNonce(1)->extractBodyFromLine('OTHER hello world');
    }
}
