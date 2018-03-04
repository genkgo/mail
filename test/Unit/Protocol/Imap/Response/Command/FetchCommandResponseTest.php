<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response\Command;

use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Response\Command\FetchCommandResponse;
use Genkgo\TestMail\AbstractTestCase;

final class FetchCommandResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_from_string()
    {
        $stub = \file_get_contents(__DIR__. '/../../../../../Stub/Imap/fetch-response.txt');

        $response = FetchCommandResponse::fromString($stub);

        $this->assertSame($stub, (string)$response);
    }

    /**
     * @test
     */
    public function it_contains_message_data()
    {
        $stub = \file_get_contents(__DIR__. '/../../../../../Stub/Imap/fetch-response.txt');
        $response = FetchCommandResponse::fromString($stub);

        $this->assertSame(10588, $response->getNumber());
        $this->assertInstanceOf(ItemList::class, $response->getDataItemList());
    }

    /**
     * @test
     */
    public function it_throws_when_not_a_fetch_command()
    {
        $this->expectException(\InvalidArgumentException::class);
        FetchCommandResponse::fromString('CAPABILITY STARTTLS');
    }
}
