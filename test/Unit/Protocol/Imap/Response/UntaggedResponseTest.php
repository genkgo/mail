<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\Mail\Protocol\Imap\Response\UntaggedResponse;
use Genkgo\TestMail\AbstractTestCase;

final class UntaggedResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_casted_to_string()
    {
        $this->assertSame(
            '* OK successful',
            (string)new UntaggedResponse('OK successful')
        );
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $response = new UntaggedResponse('OK successful');
        $this->assertNotSame($response, $response->withAddedBody('body'));
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_more_body()
    {
        $response = new UntaggedResponse('OK successful');
        $this->assertSame('OK successfully', $response->withAddedBody('ly')->getBody());
    }

    /**
     * @test
     */
    public function it_can_assert_completion_result()
    {
        $response = new UntaggedResponse('OK successful');
        $response->assertCompletion(CompletionResult::ok());
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_throws_when_invalid_completion_result_asserted()
    {
        $this->expectException(AssertionFailedException::class);
        $response = new UntaggedResponse('NO unsuccessful');
        $response->assertCompletion(CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_throws_when_body_does_not_contain_completion_result()
    {
        $this->expectException(AssertionFailedException::class);
        $response = new UntaggedResponse('hello world');
        $response->assertCompletion(CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_throws_when_asserting_continuation()
    {
        $this->expectException(AssertionFailedException::class);
        $response = new UntaggedResponse('NO unsuccessful');
        $response->assertContinuation();
    }

    /**
     * @test
     */
    public function it_throws_when_asserting_tagged()
    {
        $this->expectException(AssertionFailedException::class);
        $response = new UntaggedResponse('NO unsuccessful');
        $response->assertTagged();
    }
}
