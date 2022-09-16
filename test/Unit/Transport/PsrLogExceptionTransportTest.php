<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Transport\PsrLogExceptionTransport;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\Transport\ExceptionTransport;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class PsrLogExceptionTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_logs_an_exception(): void
    {
        $this->expectException(\RuntimeException::class);

        $exceptionTransport = new ExceptionTransport();
        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->exactly(1))
            ->method('log')
            ->with(LogLevel::INFO, 'Failed to send e-mail message. Exception class always throws an exception', [
                'exception' => [
                    'class' => 'RuntimeException',
                    'file' => \realpath(__DIR__ . '/../../Stub/Transport/ExceptionTransport.php'),
                    'line' => 16,
                    'message' => 'Exception class always throws an exception'
                ]
            ]);

        $transport = new PsrLogExceptionTransport($exceptionTransport, $logger);
        $transport->send(new PlainTextMessage('message'));
    }
}
