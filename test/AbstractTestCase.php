<?php
declare(strict_types=1);

namespace Genkgo\TestMail;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @param string $messageString
     * @return string
     */
    protected function replaceBoundaries(string $messageString): string
    {
        return \preg_replace(['/(GenkgoMailV2Part[A-Za-z0-9]*)/'], 'boundary', $messageString);
    }
}
