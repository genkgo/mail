<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\RetryIfFailedTransport;
use Genkgo\Mail\TransportInterface;

final class RetryIfFailedTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_does_not_retry_when_successful(): void
    {
        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->once())
            ->method('send');

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_retries_once_when_only_first_time_fails(): void
    {
        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->exactly(2))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new ConnectionRefusedException()),
                null
            );

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_retries_once_when_first_two_times_fail(): void
    {
        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->exactly(3))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new ConnectionRefusedException()),
                $this->throwException(new ConnectionRefusedException()),
                null
            );

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_throws_the_last_exception_when_last_retry_fails(): void
    {
        $this->expectException(ConnectionRefusedException::class);

        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->exactly(3))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }
}
