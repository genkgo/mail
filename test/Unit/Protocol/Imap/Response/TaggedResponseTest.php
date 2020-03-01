<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\Mail\Protocol\Imap\Response\TaggedResponse;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class TaggedResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_casted_to_string(): void
    {
        $this->assertSame(
            'TAG1 OK successful',
            (string)new TaggedResponse(Tag::fromNonce(1), 'OK successful')
        );
    }

    /**
     * @test
     */
    public function it_is_immutable(): void
    {
        $response = new TaggedResponse(Tag::fromNonce(1), 'OK successful');
        $this->assertNotSame($response, $response->withAddedBody('body'));
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_more_body(): void
    {
        $response = new TaggedResponse(Tag::fromNonce(1), 'OK successful');
        $this->assertSame('OK successful body', $response->withAddedBody(' body')->getBody());
    }

    /**
     * @test
     */
    public function it_can_assert_completion_result(): void
    {
        $response = new TaggedResponse(Tag::fromNonce(1), 'OK successful');
        $response->assertCompletion(CompletionResult::ok());
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_throws_when_invalid_completion_result_asserted(): void
    {
        $this->expectException(AssertionFailedException::class);
        $response = new TaggedResponse(Tag::fromNonce(1), 'NO unsuccessful');
        $response->assertCompletion(CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_throws_when_body_does_not_contain_completion_result(): void
    {
        $this->expectException(AssertionFailedException::class);
        $response = new TaggedResponse(Tag::fromNonce(1), 'hello world');
        $response->assertCompletion(CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_throws_when_asserting_continuation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $response = new TaggedResponse(Tag::fromNonce(1), 'NO unsuccessful');
        $response->assertContinuation();
    }

    /**
     * @test
     */
    public function it_does_not_throws_when_asserting_tagged(): void
    {
        $response = new TaggedResponse(Tag::fromNonce(1), 'NO unsuccessful');
        $response->assertTagged();
        $this->addToAssertionCount(1);
    }
}
