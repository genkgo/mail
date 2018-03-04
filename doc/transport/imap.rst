Send messages via IMAP
----------------------


.. code-block:: php

    $transport = new ImapTransport(
        ClientFactory::fromString('imap://user:pass@host/')->newClient(),
        new MailboxName('INBOX')
    );

    $transport->send($message);