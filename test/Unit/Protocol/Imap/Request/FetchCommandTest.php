<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\SectionItem;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\MessageData\SectionList;
use Genkgo\Mail\Protocol\Imap\Request\FetchCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class FetchCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new FetchCommand(
            Tag::fromNonce(1),
            SequenceSet::single(1),
            new ItemList([new SectionItem(new NameItem('BODY'), new SectionList())])
        );

        $this->assertSame('TAG1 FETCH 1 BODY[]', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
