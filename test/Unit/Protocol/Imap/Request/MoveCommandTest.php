<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Request\MoveCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class MoveCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream(): void
    {
        $command = new MoveCommand(
            Tag::fromNonce(1),
            SequenceSet::single(1),
            new MailboxName('INBOX.Archive')
        );

        $this->assertSame('TAG1 MOVE 1 INBOX.Archive', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
