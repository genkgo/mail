<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\TestMail\AbstractTestCase;

final class CompletionResultTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_through_valid_result_types()
    {
        CompletionResult::fromString('OK');
        CompletionResult::fromString('BAD');
        CompletionResult::fromString('NO');
        CompletionResult::ok();
        $this->addToAssertionCount(4);
    }

    /**
     * @test
     */
    public function it_throws_when_using_unknown_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        CompletionResult::fromString('UNKNOWN');
    }

    /**
     * @test
     */
    public function it_is_equals_another_result()
    {
        $result = CompletionResult::ok();
        $this->assertTrue($result->equals(CompletionResult::ok()));
        $this->assertFalse($result->equals(CompletionResult::fromString('NO')));
    }

    /**
     * @test
     */
    public function it_can_be_casted_to_string()
    {
        $this->assertSame('OK', (string)CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_line()
    {
        $this->assertSame('OK', (string)CompletionResult::fromLine('OK success'));
        $this->assertSame('OK', (string)CompletionResult::fromLine('OK'));
    }
}
