# Genkgo/Mail - PHP Mail Library

[![Build Status](https://travis-ci.org/genkgo/mail.svg)](https://travis-ci.org/genkgo/mail)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/genkgo/mail/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/genkgo/mail/)
[![Code Coverage](https://scrutinizer-ci.com/g/genkgo/mail/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/genkgo/mail/)

While analyzing what mail library to use when refactoring a code base, we discovered that the available ones are mostly
legacy libraries. Some do not use namespaces and every library we encountered were merely a collection of scalar
property bags than objects using encapsulation. It is our believe that recent vulnerabilities found in these libraries
are a consequence of that. This is not a critique to these libraries. We all used them, and used them with joy. However,
we think there is a need for new libraries that use modern principles.

Use this if you want to send e-mails over different transports and protocols using immutable messages and streams.

## Send message quick and easy

```php
$message = (new FormattedMessageFactory())
    ->withHtml('<html><body><p>Hello World</p></body></html>')
    ->withAttachment(new StringAttachment('Attachment text', 'attachment.txt', new ContentType('plain/text')))
    ->withEmbeddedImage(new EmbeddedImage(new StringStream('image'), 'pixel.gif', new ContentType('image/gif'), new ContentID('123456'))
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