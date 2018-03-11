<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Quotation;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\MessageBodyCollection;
use Genkgo\Mail\Quotation\FixedQuotation;
use Genkgo\TestMail\AbstractTestCase;

final class FixedQuotationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_quotes_other_message_body_collections()
    {
        $quotation = new FixedQuotation();

        $original = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text.eml')
        );

        $reply = new MessageBodyCollection(
            '<html><head><title>Universe Title</title></head><body>Hello Universe</body>'
        );

        $this->assertSame(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text-quoted.html'),
            $quotation->quote($reply, $original)->getHtml()
        );
    }
}
