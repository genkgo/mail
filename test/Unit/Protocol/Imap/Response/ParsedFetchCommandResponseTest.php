<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\Response\Command\ParsedFetchCommandResponse;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\TestMail\AbstractTestCase;

final class ParsedFetchCommandResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_casted_to_string(): void
    {
        $this->assertSame(
            "* 1 FETCH (BODY)\r\n",
            (string)new ParsedFetchCommandResponse(new \ArrayIterator(['* 1 FETCH (BODY)']))
        );
    }

    /**
     * @test
     */
    public function it_cannot_assert_completion_result(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('A parsed fetch command response is a completion result');

        $response = new ParsedFetchCommandResponse(new \ArrayIterator(['* 1 FETCH (BODY)']));
        $response->assertCompletion(CompletionResult::ok());
    }

    /**
     * @test
     */
    public function it_parses_multiple_lines(): void
    {
        $this->assertSame(
            "* 1 FETCH (BODY {12}\r\nHello World.)\r\n",
            (string)new ParsedFetchCommandResponse(new \ArrayIterator(["* 1 FETCH (BODY {12}\r\n", "Hello World.)"]))
        );
    }

    /**
     * @test
     */
    public function it_has_the_message_number(): void
    {
        $parsedResponse = new ParsedFetchCommandResponse(
            new \ArrayIterator(["* 1 FETCH (BODY {12}\r\n", "Hello World.)"])
        );

        $this->assertSame(
            1,
            $parsedResponse->getNumber()
        );
    }

    /**
     * @test
     */
    public function it_has_the_item_list_number(): void
    {
        $parsedResponse = new ParsedFetchCommandResponse(
            new \ArrayIterator(["* 1 FETCH (BODY {12}\r\n", "Hello World.)"])
        );

        $this->assertSame(
            "(BODY {12}\r\nHello World.)",
            (string)$parsedResponse->getItemList()
        );
    }
}
