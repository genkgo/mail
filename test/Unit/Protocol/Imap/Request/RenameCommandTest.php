<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Request\RenameCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class RenameCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new RenameCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX.Archive'),
            new MailboxName('INBOX.Archive2017')
        );

        $this->assertSame('TAG1 RENAME INBOX.Archive INBOX.Archive2017', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
