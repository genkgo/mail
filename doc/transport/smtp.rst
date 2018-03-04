Send messages via SMTP
----------------------

.. code-block:: php

    $transport = new SmtpTransport(
        ClientFactory::fromString('smtp://user:pass@host/')->newClient(),
        EnvelopeFactory::useExtractedHeader()
    );

    $transport->send($message);