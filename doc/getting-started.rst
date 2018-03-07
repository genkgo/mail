Getting started
===============

Installation
------------

Install the library using composer. Execute the following command in your command line (in the project root).

.. code-block:: bash

    $ composer require genkgo/mail


Message Instantiation
---------------------

Use the formatted message factory to create a message

.. code-block:: php

    $message = (new MessageBodyCollection())
        ->withHtml('<html><body><p>Hello World</p></body></html>')
        ->withAttachment(new FileAttachment('/order1.pdf', new ContentType('application/pdf')))
        ->createMessage()
        ->withHeader(new Subject('Hello World'))
        ->withHeader(From::fromEmailAddress('from@example.com'))
        ->withHeader(To::fromSingleRecipient('to@example.com', 'name'))
        ->withHeader(Cc::fromSingleRecipient('cc@example.com', 'name'));

Send messages via SMTP
----------------------

To send the message use a transport, e.g. the SMTP transport.

.. code-block:: php

    $transport = new SmtpTransport(
        ClientFactory::fromString('smtp://user:pass@host/')->newClient(),
        EnvelopeFactory::useExtractedHeader()
    );

    $transport->send($message);
