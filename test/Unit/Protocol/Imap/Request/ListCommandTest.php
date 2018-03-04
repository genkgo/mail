<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MailboxWildcard;
use Genkgo\Mail\Protocol\Imap\Request\ListCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class ListCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new ListCommand(
            Tag::fromNonce(1),
            new MailboxWildcard('~smith/Mail/'),
            new MailboxWildcard('foo.*')
        );

        $this->assertSame('TAG1 LIST ~smith/Mail/ foo.*', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }

    /**
     * @test
     */
    public function it_allows_empty_searches()
    {
        $command = new ListCommand(
            Tag::fromNonce(1),
            new MailboxWildcard(''),
            new MailboxWildcard('')
        );

        $this->assertSame('TAG1 LIST "" ""', (string)$command->toStream());
    }
}
