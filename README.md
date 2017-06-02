# Genkgo/Mail - PHP Mail Library

[![Build Status](https://travis-ci.org/genkgo/mail.svg)](https://travis-ci.org/genkgo/mail)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/genkgo/mail/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/genkgo/mail/)
[![Code Coverage](https://scrutinizer-ci.com/g/genkgo/mail/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/genkgo/mail/)

While analyzing what mail library to use when refactoring a code base, we discovered that the available ones are mostly
legacy libraries. Some do not use namespaces and every library we encountered were merely a collection of scalar
property bags than objects using encapsulation. This is not a critique to these libraries. We all used them, and used
them with joy. However, we think there is a need for new libraries that use modern principles.

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
    ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@example.com'), 'name')])))
    ->withHeader(new Cc(new AddressList([new Address(new EmailAddress('cc@example.com'), 'name')])));

$transport = new SmtpTransport(
    ClientFactory::fromString('smtp+tls://user:pass@host/')->newClient(),
    EnvelopeFactory::useExtractedHeader()
);

$transport->send($message);
```


## Features

- Use SMTP or mail() to send messages
- Queue messages when transport fails
- Reconnect automatically after interval
- Optimal encoded headers, so no excessive (Q/B) encoded headers
- Optimal encoded multipart messages
- Only streams and connections are mutable
- Messages and actors are immutable
- Value objects protect against invalid states
- Streams make sure the library has a low memory burden
- Many objects but still easy API
- 90%+ test coverage
- Security is high prioritized
- Great RFC compliance
- Cast messages to valid string source
- Library has no external dependencies
- Only PHP 7.1 and up


## Upcoming features

- DKIM headers
- Encrypted and signed messages


## RFC-compliance
 
This library tends to be as compliant with e-mail RFCs as possible. It should be compliant with the following RFCs.

- [RFC 1896, The text/enriched MIME Content-type](https://tools.ietf.org/html/rfc1896)
- [RFC 2822, Internet Message Format](https://tools.ietf.org/html/rfc2822)
- [RFC 2045, Multipurpose Internet Mail Extensions (MIME) Part One](https://tools.ietf.org/html/rfc2045)
- [RFC 2046, Multipurpose Internet Mail Extensions (MIME) Part Two](https://tools.ietf.org/html/rfc2046)
- [RFC 2047, Multipurpose Internet Mail Extensions (MIME) Part Three](https://tools.ietf.org/html/rfc2047)
- [RFC 2048, Multipurpose Internet Mail Extensions (MIME) Part Four](https://tools.ietf.org/html/rfc2048)
- [RFC 2049, Multipurpose Internet Mail Extensions (MIME) Part Five](https://tools.ietf.org/html/rfc2049)
- [RFC 2821, Simple Mail Transfer Protocol](https://tools.ietf.org/html/rfc2821)
- [RCC 4954, SMTP Service Extension for Authentication](https://tools.ietf.org/html/rfc4954)
- [RFC 5321, Simple Mail Transfer Protocol](https://tools.ietf.org/html/rfc5321)


## Credits

This library was not able to exist without [Zend/Mail](https://github.com/zendframework/zend-mail)
and [PHPMailer](https://github.com/PHPMailer/PHPMailer).