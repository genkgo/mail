<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Flag;
use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\FlagsItem;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Request\StoreCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class StoreCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new StoreCommand(
            Tag::fromNonce(1),
            SequenceSet::single(1),
            new FlagsItem(new FlagParenthesizedList([new Flag('\\Answered')]))
        );

        $this->assertSame('TAG1 STORE 1 FLAGS (\Answered)', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
