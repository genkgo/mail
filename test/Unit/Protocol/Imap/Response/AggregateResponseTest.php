<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Request\FetchCommand;
use Genkgo\Mail\Protocol\Imap\Request\NoopCommand;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\Mail\Protocol\Imap\Response\AggregateResponse;
use Genkgo\Mail\Protocol\Imap\Response\Command\ParsedFetchCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CommandContinuationRequestResponse;
use Genkgo\Mail\Protocol\Imap\Response\TaggedResponse;
use Genkgo\Mail\Protocol\Imap\Response\UntaggedResponse;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class AggregateResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_iterated(): void
    {
        $this->assertTrue(\is_iterable(new AggregateResponse()));
        $this->assertInstanceOf(
            \Traversable::class,
            (new AggregateResponse())->getIterator()
        );
    }

    /**
     * @test
     */
    public function it_throws_when_last_with_empty_response(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        (new AggregateResponse())->last();
    }

    /**
     * @test
     */
    public function it_throws_when_at_with_empty_response(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        (new AggregateResponse())->at(0);
    }

    /**
     * @test
     */
    public function it_can_return_the_first_line(): void
    {
        $this->assertInstanceOf(
            TaggedResponse::class,
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->first()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_first_line_with_multiple_lines(): void
    {
        $this->assertInstanceOf(
            UntaggedResponse::class,
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["* TEST\r\n", "TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->first()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_last_line(): void
    {
        $this->assertInstanceOf(
            TaggedResponse::class,
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->last()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_last_line_with_multiple_lines(): void
    {
        $this->assertInstanceOf(
            TaggedResponse::class,
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["* TEST\r\n", "TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->last()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_line_at_with_multiple_lines(): void
    {
        $this->assertInstanceOf(
            UntaggedResponse::class,
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["* TEST\r\n", "TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->at(0)
        );

        $this->assertInstanceOf(
            TaggedResponse::class,
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["* TEST\r\n", "TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->at(1)
        );
    }

    /**
     * @test
     */
    public function it_knows_when_response_is_completed(): void
    {
        $this->assertFalse(
            (new AggregateResponse())->hasCompleted()
        );

        $this->assertTrue(
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->hasCompleted()
        );

        $this->assertTrue(
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["* TEST\r\n", "TAG1 OK\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->hasCompleted()
        );

        $this->assertTrue(
            AggregateResponse::fromResponseLines(
                new \ArrayIterator(["+ continue\r\n"]),
                new NoopCommand(Tag::fromNonce(1))
            )->hasCompleted()
        );
    }

    /**
     * @test
     */
    public function it_defines_an_untagged_response(): void
    {
        $response = AggregateResponse::fromResponseLines(
            new \ArrayIterator(["* Untagged\r\n", "TAG1 OK"]),
            new NoopCommand(Tag::fromNonce(1))
        );

        $this->assertInstanceOf(UntaggedResponse::class, $response->first());
        $this->assertSame("Untagged\r\n", $response->first()->getBody());
    }

    /**
     * @test
     */
    public function it_defines_a_tagged_response(): void
    {
        $response = AggregateResponse::fromResponseLines(
            new \ArrayIterator(["TAG1 OK\r\n"]),
            new NoopCommand(Tag::fromNonce(1))
        );

        $this->assertInstanceOf(TaggedResponse::class, $response->first());
        $this->assertSame("OK\r\n", $response->first()->getBody());
    }

    /**
     * @test
     */
    public function it_defines_a_continuation_response(): void
    {
        $response = AggregateResponse::fromResponseLines(
            new \ArrayIterator(["+ Please continue\r\n"]),
            new NoopCommand(Tag::fromNonce(1))
        );

        $this->assertInstanceOf(CommandContinuationRequestResponse::class, $response->first());
        $this->assertSame("Please continue\r\n", $response->first()->getBody());
    }

    /**
     * @test
     */
    public function it_deals_with_multi_line_untagged_response(): void
    {
        $fetch = \trim(\file_get_contents(__DIR__. '/../../../../Stub/Imap/fetch-response.crlf.txt'));
        $responseString = '* ' . $fetch;
        $lines = \array_map(
            function (string $line) {
                return $line . "\r\n";
            },
            \preg_split('/(\r\n)/', $responseString),
        );
        $lines[] = "TAG1 OK FETCH complete\r\n";

        $response = AggregateResponse::fromResponseLines(
            new \ArrayIterator($lines),
            new FetchCommand(
                Tag::fromNonce(1),
                SequenceSet::infiniteRange(1),
                ItemList::fromString('(BODY[])')
            )
        );

        $this->assertInstanceOf(ParsedFetchCommandResponse::class, $response->first());
        $this->assertInstanceOf(TaggedResponse::class, $response->last());
        $this->assertSame($fetch, $response->first()->getBody());
    }

    /**
     * @test
     */
    public function it_deals_with_multi_fetch_response(): void
    {
        $fetch = \trim(\file_get_contents(__DIR__. '/../../../../Stub/Imap/fetch-response.crlf.txt'));
        $responseString = '* ' . $fetch;
        /** @var array<int, string> $lines */
        $lines = \array_map(
            function (string $line) {
                return $line . "\r\n";
            },
            \preg_split('/(\r\n)/', $responseString),
        );
        $lines = \array_merge($lines, $lines);
        $lines[] = "TAG1 OK FETCH complete\r\n";

        $response = AggregateResponse::fromResponseLines(
            new \ArrayIterator($lines),
            new FetchCommand(
                Tag::fromNonce(1),
                SequenceSet::infiniteRange(1),
                ItemList::fromString('(BODY[])')
            )
        );

        $this->assertInstanceOf(ParsedFetchCommandResponse::class, $response->at(0));
        $this->assertInstanceOf(ParsedFetchCommandResponse::class, $response->at(1));
        $this->assertInstanceOf(TaggedResponse::class, $response->last());
        $this->assertSame($fetch, $response->first()->getBody());
    }
}
