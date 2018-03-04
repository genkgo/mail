<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Request\StatusCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class StatusCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new StatusCommand(
            Tag::fromNonce(1),
            new MailboxName('INBOX'),
            new ItemList([new NameItem('UIDNEXT'), new NameItem('MESSAGES')])
        );

        $this->assertSame('TAG1 STATUS INBOX (UIDNEXT MESSAGES)', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
