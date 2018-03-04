<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\ExpungeCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class ExpungeCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new ExpungeCommand(Tag::fromNonce(1));

        $this->assertSame('TAG1 EXPUNGE', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
