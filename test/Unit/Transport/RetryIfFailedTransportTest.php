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
    public function it_does_not_retry_when_successful()
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
    public function it_retries_once_when_only_first_time_fails()
    {
        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->at(0))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $decoratedTransport
            ->expects($this->at(1))
            ->method('send');

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_retries_once_when_first_two_times_fail()
    {
        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->at(0))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $decoratedTransport
            ->expects($this->at(1))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $decoratedTransport
            ->expects($this->at(2))
            ->method('send');

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_throws_the_last_exception_when_last_retry_fails()
    {
        $this->expectException(ConnectionRefusedException::class);

        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->at(0))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $decoratedTransport
            ->expects($this->at(1))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $decoratedTransport
            ->expects($this->at(2))
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $sender = new RetryIfFailedTransport($decoratedTransport, 3);
        $sender->send(new GenericMessage());
    }
}
