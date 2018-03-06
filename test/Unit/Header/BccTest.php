<?php 
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Bcc;

final class BccTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_correct_values()
    {
        $header = new Bcc(
            new AddressList([
                new Address(
                    new EmailAddress('me@example.com'),
                    'Name'
                )
            ])
        );

        $this->assertEquals('Bcc', (string)$header->getName());
        $this->assertEquals('Name <me@example.com>', (string)$header->getValue());
    }
}
