<?php
declare(strict_types=1);

namespace Genkgo\Mail;

interface QuotationInterface
{
    /**
     * @param MessageBodyCollection $body
     * @param MessageInterface $originalMessage
     * @return MessageBodyCollection
     */
    public function quote(MessageBodyCollection $body, MessageInterface $originalMessage): MessageBodyCollection;
}
