<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\Backend\DevNullBackend;
use Genkgo\TestMail\AbstractTestCase;

final class DevNullBackendTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_contains_addresses()
    {
        $backend = new DevNullBackend();

        $this->assertTrue($backend->contains(new EmailAddress('test@genkgo.nl')));
        $this->assertTrue($backend->contains(new EmailAddress('other@genkgo.nl')));
    }

    /**
     * @test
     */
    public function it_stores_messages()
    {
        $backend = new DevNullBackend();

        $backend->store(
            new EmailAddress('test@genkgo.nl'),
            new PlainTextMessage('test'),
            'INBOX'
        );

        $this->assertTrue(true);
    }
}
