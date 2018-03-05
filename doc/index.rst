Genkgo/Mail - Modern PHP 7.1+ Mail Library
==========================================

Library to send e-mails over different transports and protocols (like SMTP) using immutable messages and streams. Also
includes SMTP server.


Why another Mail library for PHP?
---------------------------------

While analyzing what mail library to use when refactoring a code base, we discovered that the available ones are mostly
legacy libraries. Some do not use namespaces and every library we encountered was merely a collection of scalar property
bags than objects using encapsulation. Although we used these libs with joy in the past, they do not meet current quality
standards. So, we built a new and better library according to modern programming principles.

Use this if you want to send e-mails over different transports and protocols using immutable messages and streams.


.. toctree::
    :hidden:

    Home <self>
    getting-started

.. toctree::
    :hidden:
    :caption: Transport
    :maxdepth: 3

    transport/smtp
    transport/mail
    transport/imap
    transport/file
    transport/other

.. toctree::
    :hidden:
    :caption: Protocol
    :maxdepth: 3

    protocol/imap
    protocol/smtp

.. toctree::
    :hidden:
    :caption: Advanced
    :maxdepth: 3

    advanced/dkim
    advanced/queue

.. |clearfloat|  raw:: html

    <div style="clear:left"></div>
