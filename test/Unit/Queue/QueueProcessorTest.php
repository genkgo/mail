<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\Mail\Queue\QueueProcessor;
use Genkgo\Mail\Queue\QueueProcessorInterface;

final class QueueProcessorTest extends AbstractQueueProcessorTest
{
    protected function getQueueProcessor($transport, array $queue): QueueProcessorInterface
    {
        return new QueueProcessor($transport, $queue);
    }
}
