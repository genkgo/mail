<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Request\CopyCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class CopyCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new CopyCommand(
            Tag::fromNonce(1),
            SequenceSet::single(1),
            new MailboxName('INBOX.Archive')
        );

        $this->assertSame('TAG1 COPY 1 INBOX.Archive', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
