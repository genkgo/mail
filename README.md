# Genkgo/Mail - PHP Mail Library

[![Build Status](https://travis-ci.org/genkgo/mail.svg)](https://travis-ci.org/genkgo/mail)

While analyzing what mail library to use when refactoring a code base, we discovered that there are many legacy
libraries where some do not use namespaces and every library is merely a collection of scalar property bags. Recent
vulnerabilities found in these libraries is a consequence of that. This is not a critique to these libraries because
we all used them, and used them with joy. However, we think there is a need for new libraries to use modern principles.

Use this if you want to send e-mails over different transports and protocols using immutable messages and streams.

## Send message quick and easy

```php
$message = (new FormattedMessageFactory())
    ->withHtml('<html><body><p>Hello World</p></body></html>')
    ->createMessage()
    ->withHeader(new From(new Address(new EmailAddress('from@example.com'), 'name')))
    ->withHeader(new Subject('Hello World'))
    ->withHeader(new To([new Address(new EmailAddress('to@example.com'), 'name')]))
    ->withHeader(new Cc([new Address(new EmailAddress('cc@example.com'), 'name')]));

$transport = new NullTransport();
$transport->send($message);
```

## Credits

This library was not able to exist without [Zend/Mail](https://github.com/zendframework/zend-mail)
and [PHPMailer](https://github.com/PHPMailer/PHPMailer).