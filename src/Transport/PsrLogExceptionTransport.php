<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class PsrLogExceptionTransport
{
    /**
     * @var TransportInterface
     */
    private $delegatedTransport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $logLevel;

    /**
     * @param TransportInterface $delegatedTransport
     * @param LoggerInterface $logger
     * @param string $logLevel
     */
    public function __construct(TransportInterface $delegatedTransport, LoggerInterface $logger, string $logLevel = LogLevel::INFO)
    {
        $this->delegatedTransport = $delegatedTransport;
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message): void
    {
        try {
            $this->delegatedTransport->send($message);
        } catch (\Throwable $e) {
            $this->logger->log($this->logLevel, 'Failed to send e-mail message. ' . $e->getMessage(), [
                'exception' => [
                    'class' => \get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }
}
