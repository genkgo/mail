<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Protocol\Imap\Response\AggregateResponse;
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
    public function it_can_be_iterated()
    {
        $this->assertTrue(\is_iterable(new AggregateResponse(Tag::fromNonce(1))));
        $this->assertInstanceOf(
            \Traversable::class,
            (new AggregateResponse(Tag::fromNonce(1)))->getIterator()
        );
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $response = new AggregateResponse(Tag::fromNonce(1));
        $this->assertNotSame($response, $response->withLine('* Untagged'));
    }

    /**
     * @test
     */
    public function it_throws_when_first_with_empty_response()
    {
        $this->expectException(\OutOfBoundsException::class);

        (new AggregateResponse(Tag::fromNonce(1)))->first();
    }

    /**
     * @test
     */
    public function it_throws_when_last_with_empty_response()
    {
        $this->expectException(\OutOfBoundsException::class);

        (new AggregateResponse(Tag::fromNonce(1)))->last();
    }

    /**
     * @test
     */
    public function it_throws_when_at_with_empty_response()
    {
        $this->expectException(\OutOfBoundsException::class);

        (new AggregateResponse(Tag::fromNonce(1)))->at(0);
    }

    /**
     * @test
     */
    public function it_can_return_the_first_line()
    {
        $this->assertInstanceOf(
            UntaggedResponse::class,
            (new AggregateResponse(Tag::fromNonce(1)))->withLine('* FETCH')->first()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_first_line_with_multiple_lines()
    {
        $this->assertInstanceOf(
            TaggedResponse::class,
            (new AggregateResponse(Tag::fromNonce(1)))
                ->withLine('TAG1 FETCH')
                ->withLine('* FETCH')
                ->first()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_last_line()
    {
        $this->assertInstanceOf(
            UntaggedResponse::class,
            (new AggregateResponse(Tag::fromNonce(1)))->withLine('* FETCH')->last()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_last_line_with_multiple_lines()
    {
        $this->assertInstanceOf(
            UntaggedResponse::class,
            (new AggregateResponse(Tag::fromNonce(1)))
                ->withLine('TAG1 FETCH')
                ->withLine('* FETCH')
                ->last()
        );
    }

    /**
     * @test
     */
    public function it_can_return_the_line_at_with_multiple_lines()
    {
        $tag = Tag::fromNonce(1);

        $this->assertInstanceOf(
            TaggedResponse::class,
            (new AggregateResponse($tag))
                ->withLine('TAG1 FETCH')
                ->withLine('* FETCH')
                ->at(0)
        );

        $this->assertInstanceOf(
            UntaggedResponse::class,
            (new AggregateResponse($tag))
                ->withLine('TAG1 FETCH')
                ->withLine('* FETCH')
                ->at(1)
        );
    }

    /**
     * @test
     */
    public function it_knows_when_response_is_completed()
    {
        $tag = Tag::fromNonce(1);

        $this->assertFalse(
            (new AggregateResponse($tag))->hasCompleted()
        );

        $this->assertFalse(
            (new AggregateResponse($tag))
                ->withLine('TAG1 FETCH')
                ->withLine('* FETCH')
                ->hasCompleted()
        );

        $this->assertTrue(
            (new AggregateResponse($tag))
                ->withLine('* FETCH')
                ->withLine('TAG1 FETCH')
                ->hasCompleted()
        );

        $this->assertTrue(
            (new AggregateResponse($tag))
                ->withLine('+ Continue')
                ->hasCompleted()
        );
    }

    /**
     * @test
     */
    public function it_defines_an_untagged_response()
    {
        $response = new AggregateResponse(Tag::fromNonce(1));
        $line = $response->withLine('* Untagged')->first();

        $this->assertInstanceOf(UntaggedResponse::class, $line);
        $this->assertSame('Untagged', $line->getBody());
    }

    /**
     * @test
     */
    public function it_defines_a_tagged_response()
    {
        $response = new AggregateResponse(Tag::fromNonce(1));
        $line = $response->withLine('TAG1 OK')->first();

        $this->assertInstanceOf(TaggedResponse::class, $line);
        $this->assertSame('OK', $line->getBody());
    }

    /**
     * @test
     */
    public function it_defines_a_continuation_response()
    {
        $response = new AggregateResponse(Tag::fromNonce(1));
        $line = $response->withLine('+ Please continue')->first();

        $this->assertInstanceOf(CommandContinuationRequestResponse::class, $line);
        $this->assertSame('Please continue', $line->getBody());
    }

    /**
     * @test
     */
    public function it_deals_with_multi_line_untagged_response()
    {
        $response = new AggregateResponse(Tag::fromNonce(1));
        $fetch = \file_get_contents(__DIR__. '/../../../../Stub/Imap/fetch-response.txt');

        $responseString = '* ' . $fetch;
        foreach (\preg_split('/(\n)/', $responseString, -1, PREG_SPLIT_DELIM_CAPTURE) as $line) {
            $response = $response->withLine($line);
        }

        $response = $response->withLine('TAG1 OK FETCH complete');

        $this->assertInstanceOf(UntaggedResponse::class, $response->first());
        $this->assertInstanceOf(TaggedResponse::class, $response->last());
        $this->assertSame($fetch, $response->first()->getBody());
    }

    /**
     * @test
     */
    public function it_can_be_casted_to_string()
    {
        $response = new AggregateResponse(Tag::fromNonce(1));
        $fetch = \file_get_contents(__DIR__. '/../../../../Stub/Imap/fetch-response.txt');

        $responseString = '* ' . $fetch;
        foreach (\preg_split('/(\n)/', $responseString, -1, PREG_SPLIT_DELIM_CAPTURE) as $line) {
            $response = $response->withLine($line);
        }

        $response = $response->withLine('TAG1 OK FETCH complete');

        $this->assertInstanceOf(UntaggedResponse::class, $response->first());
        $this->assertInstanceOf(TaggedResponse::class, $response->last());
        $this->assertSame($fetch, $response->first()->getBody());
        $this->assertSame($responseString."\r\nTAG1 OK FETCH complete", (string)$response);
    }

    /**
     * @test
     */
    public function it_throws_when_adding_content_line_when_empty_response()
    {
        $this->expectException(\UnexpectedValueException::class);

        (new AggregateResponse(Tag::fromNonce(1)))->withLine('content');
    }
}
