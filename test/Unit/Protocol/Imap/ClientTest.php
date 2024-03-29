<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\NegotiationInterface;
use Genkgo\Mail\Protocol\Imap\Request\NoopCommand;
use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\Mail\Protocol\Imap\TagFactoryInterface;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\TestMail\AbstractTestCase;

final class ClientTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_will_register_negotiators(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $negotiator = $this->createMock(NegotiationInterface::class);

        $negotiator
            ->expects($this->exactly(1))
            ->method('negotiate');

        $connection
            ->expects($this->exactly(1))
            ->method('addListener')
            ->with(
                'connect',
                $this->callback(
                    function (\Closure $closure) {
                        $closure();
                        return true;
                    }
                )
            );

        new Client($connection, new GeneratorTagFactory(), [$negotiator]);
    }

    /**
     * @test
     */
    public function it_will_issue_new_tags(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $factory = $this->createMock(TagFactoryInterface::class);

        $factory
            ->expects($this->exactly(1))
            ->method('newTag')
            ->willReturn(Tag::fromNonce(1));

        $client = new Client($connection, $factory);
        $client->newTag();
    }

    /**
     * @test
     */
    public function it_will_emit_commands(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('send')
            ->with("TAG1 NOOP\r\n");

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willReturn("TAG1 OK\r\n");

        $client = new Client($connection, new GeneratorTagFactory());
        $client->emit(new NoopCommand($client->newTag()));
    }

    /**
     * @test
     */
    public function it_will_split_commands_per_line(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $command = $this->createMock(RequestInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["TAG1 OK COMMAND\r\n"],
                ["MORE DATA\r\n"]
            );

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willReturn("TAG1 OK\r\n");

        $command
            ->expects($this->exactly(1))
            ->method('toStream')
            ->willReturn(new StringStream("TAG1 OK COMMAND\r\nMORE DATA"));

        $command
            ->expects($this->any())
            ->method('getTag')
            ->willReturn(Tag::fromNonce(1));

        $client = new Client($connection, new GeneratorTagFactory());
        $client->emit($command);
    }
}
