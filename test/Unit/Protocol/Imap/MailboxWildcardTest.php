<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Protocol\Imap\MailboxWildcard;
use Genkgo\TestMail\AbstractTestCase;

final class MailboxWildcardTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_accepts_default_names()
    {
        new MailboxWildcard('INBOX');
        new MailboxWildcard('INBOX.Sent');
        new MailboxWildcard('ARCHIVE');
        new MailboxWildcard('ARCHIVE2018');
        $this->addToAssertionCount(4);
    }

    /**
     * @test
     */
    public function it_accepts_spaces_if_quoted()
    {
        new MailboxWildcard('"Archive 2018"');
        new MailboxWildcard('INBOX."Archive 2018"');
        $this->addToAssertionCount(2);
    }

    /**
     * @test
     */
    public function it_does_not_allow_unquoted_space()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MailboxWildcard('Archive 2018');
    }

    /**
     * @test
     */
    public function it_allows_unquoted_wildcard_percentage()
    {
        new MailboxWildcard('Archive%2018');
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_allows_unquoted_wildcard_asterisk()
    {
        new MailboxWildcard('Archive*2018');
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_can_be_casted_to_string()
    {
        $this->assertSame('"Archive*2018"', (string)new MailboxWildcard('"Archive*2018"'));
    }

    /**
     * @test
     */
    public function it_allows_an_empty_string()
    {
        new MailboxWildcard('');
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_throws_when_unfinished_literal()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MailboxWildcard('Archive."Archive 2018');
    }

    /**
     * @test
     */
    public function it_throws_when_using_8bit_name_when_quoted()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MailboxWildcard('Archive."' . "\u{1000}" . '"');
    }
}
