<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\Request\CreateCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class CreateCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new CreateCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX.Archive')
        );

        $this->assertSame('TAG1 CREATE INBOX.Archive', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
