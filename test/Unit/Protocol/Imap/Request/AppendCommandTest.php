<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Flag;
use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Request\AppendCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class AppendCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new AppendCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX.Archive'),
            100
        );

        $this->assertSame('TAG1 APPEND INBOX.Archive {100}', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }

    /**
     * @test
     */
    public function it_creates_a_stream_with_flags()
    {
        $command = new AppendCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX.Archive'),
            100,
            new FlagParenthesizedList([new Flag('\\Answered')])
        );

        $this->assertSame('TAG1 APPEND INBOX.Archive (\\Answered) {100}', (string)$command->toStream());
    }

    /**
     * @test
     */
    public function it_creates_a_stream_with_flags_and_internal_date()
    {
        $command = new AppendCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX.Archive'),
            100,
            new FlagParenthesizedList([new Flag('\\Answered')]),
            new \DateTimeImmutable('2015-01-01')
        );

        $this->assertSame(
            'TAG1 APPEND INBOX.Archive (\\Answered) "01-Thu-2015 00:00:00+0000" {100}',
            (string)$command->toStream()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_stream_with_only_internal_date()
    {
        $command = new AppendCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX.Archive'),
            100,
            null,
            new \DateTimeImmutable('2015-01-01')
        );

        $this->assertSame(
            'TAG1 APPEND INBOX.Archive "01-Thu-2015 00:00:00+0000" {100}',
            (string)$command->toStream()
        );
    }
}
