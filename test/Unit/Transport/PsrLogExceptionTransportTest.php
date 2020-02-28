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
    public function throw_exception()
    {
        $this->expectException(\RuntimeException::class);

        $exceptionTransport = new ExceptionTransport();
        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->at(0))
            ->method('log')
            ->with(LogLevel::INFO, 'Failed to send e-mail message. Exception class always throws an exception', [
                'exception' => [
                    'class' => 'RuntimeException',
                    'file' => '/srv/libraries/mail/test/Stub/Transport/ExceptionTransport.php',
                    'line' => 16,
                    'message' => 'Exception class always throws an exception'
                ]
            ]);

        $transport = new PsrLogExceptionTransport($exceptionTransport, $logger);
        $transport->send(new PlainTextMessage('message'));
    }
}
