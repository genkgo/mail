<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\NoopCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class NoopCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new NoopCommand(Tag::fromNonce(1));

        $this->assertSame('TAG1 NOOP', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
