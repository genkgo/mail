<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\Response\CommandContinuationRequestResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\TestMail\AbstractTestCase;

final class CommandContinuationRequestResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_casted_to_string(): void
    {
        $this->assertSame(
            '+ OK successful',
            (string)new CommandContinuationRequestResponse('OK successful')
        );
    }

    /**
     * @test
     */
    public function it_is_mutable(): void
    {
        $response = new CommandContinuationRequestResponse('OK successful');
        $this->assertSame($response, $response->withAddedBody('body'));
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_more_body(): void
    {
        $response = new CommandContinuationRequestResponse('OK successful');
        $this->assertSame('OK successfully', $response->withAddedBody('ly')->getBody());
    }

    /**
     * @test
     */
    public function it_throws_when_asserting_completion(): void
    {
        $this->expectException(AssertionFailedException::class);
        $response = new CommandContinuationRequestResponse('OK hello world');
        $response->assertCompletion(CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_does_not_throw_when_asserting_continuation(): void
    {
        $response = new CommandContinuationRequestResponse('NO unsuccessful');
        $response->assertContinuation();
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_throws_when_asserting_tagged(): void
    {
        $this->expectException(AssertionFailedException::class);
        $response = new CommandContinuationRequestResponse('NO unsuccessful');
        $response->assertTagged();
    }
}
