<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Protocol\Imap\Flag;
use Genkgo\TestMail\AbstractTestCase;

final class FlagTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_allows_rfc_fixed()
    {
        new Flag('\\Answered');
        new Flag('\\Flagged');
        new Flag('\\Deleted');
        new Flag('\\Seen');
        new Flag('\\Draft');
        $this->addToAssertionCount(5);
    }

    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame('Keyword', (string)new Flag('Keyword'));
    }

    /**
     * @test
     */
    public function it_allows_keywords()
    {
        new Flag('Keyword');
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_allows_custom_flags()
    {
        new Flag('\\OtherFlag');
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_throws_when_using_atom_special()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Flag('\\Flag(');
    }

    /**
     * @test
     */
    public function it_throws_when_using_space()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Flag('\\Flag Test');
    }

    /**
     * @test
     */
    public function it_throws_when_using_atom_special_in_keyword()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Flag('Flag(');
    }

    /**
     * @test
     */
    public function it_throws_when_using_space_in_keyword()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Flag('Flag Test');
    }

    /**
     * @test
     */
    public function it_throws_when_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Flag('');
    }

    /**
     * @test
     */
    public function it_equals_another_value()
    {
        $flag = new Flag('\\Answered');
        $this->assertTrue($flag->equals(new Flag('\\Answered')));
    }

    /**
     * @test
     */
    public function it_knows_when_keyword()
    {
        $flag = new Flag('\\Answered');
        $keyword = new Flag('Test');

        $this->assertFalse($flag->isKeyword());
        $this->assertTrue($keyword->isKeyword());
    }
}
