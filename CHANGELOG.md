# Change Log


All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [2.12.3] - 2025-01-28

### Fixed

- Add ReturnPath class for the Return-Path header


## [2.12.2] - 2025-01-24

### Fixed

- Fix missing methods getPunyCodeLocalPart and getPunyCodeDomain


## [2.12.1] - 2024-05-31

### Fixed

- Transfer Content Encoding might not be a lowered case string when reading part


## [2.12.0] - 2024-03-18

### Added

- PHP 8.3 support

### Changed

- Dropped PHP 8.0
- Folding header lines after header name


## [2.11.0] - 2023-03-01

### Added

- PHP 8.2 support

### Changed

- Dropped PHP 7.4


## [2.10.1] - 2022-10-04

### Fixed

- Too much remaining white-space in alternative text


## [2.10.0] - 2022-09-16

### Added

- Option to ignore headers during DKIM signing 

### Changed

- Upgraded PHPUnit to v9
- Upgraded PHPStan to v1


## [2.9.3] - 2022-07-27

### Fixed

- Extracting HTML from messages with multiple HTML parts


## [2.9.2] - 2022-06-06

### Fixed

- Fix AddressList::getIterator for PHP 8.1


## [2.9.1] - 2022-03-18

### Fixed

- Fix ampersand bug with quoted printable subjects


## [2.9.0] - 2022-02-18

### Added

- Added PHP 8.1 support
- Make GenericMessage::fromString more memory efficient

### Changed

- Drop PHP 7.3


## [2.8.0] - 2021-05-25

### Added

- Added AlternativeText::fromRawString


## [2.7.16] - 2021-04-13

### Added

- Support PHP 8.0


## [2.7.15 - 2.7.3]

### Fixed

- Multiple parsing and IMAP fixes


## [2.7.2] - 2020-09-17

### Fixed

- Parsing headers with semiscolon that are unstructured, like a received header


## [2.7.0] - 2020-09-17

### Fixed

- When decoding a quoted message, the mime parts might require transfer decoding
- Error when quoting a HTML message without a body t

### Added

- IMAP MOVE command


## [2.6.6] - 2020-09-16

### Fixed

- Another Hotmail subject header fix


## [2.6.5] - 2020-09-16

### Fixed

- Fix q encoding subject header for Hotmail


## [2.6.4] - 2020-09-04

### Fixed

- Use smtp-starttls to completely disable starttls and use smtp+starttls to try upgrading to secure connection when advertised or when using legacy helo.


## [2.6.3] - 2020-09-03

### Fixed

- Fix for missing try/catch


## [2.6.2] - 2020-09-01

### Fixed

- Allow failure STARTTLS when using TryTls
- Fix question mark in quoted printable encoded headers

### Added

- Context options to PlainTcpConnection


## [2.6.1] - 2020-04-19

### Fixed

- Fix long attachment filenames (#67)


## [2.6.0] - 2020-04-01

### Added

- Added PsrLogExceptionTransport

### Changed

- Upgraded PHPStan to version 0.12, various fixes 

### Fixed

- Fix space showing up in subject words due message folding (#66)


## [2.5.7] - 2020-03-13

### Fixed

- Supports streams for FilesystemQueue.


## [2.5.6] - 2019-12-12

### Fixed

- Fixes address containing quotes on the folded line


## [2.5.5] - 2019-12-12

### Fixed

- Fix header value parsing, should remove line separations


## [2.5.4] - 2019-02-18

### Fixed

- Allow special characters in filenames of content disposition.
 

## [2.5.3] - 2019-02-14

### Changed

- Increases verbosity of message when an exception occurs due to an invalid header value parameter.


## [2.5.2] - 2018-10-30

### Fixed

- Allow addresses and headers to contain multi-byte strings that contain a new line when interpreted as single bytes.


## [2.5.1] - 2018-06-04

### Fixed

- From header was encoded twice, caused problems with very long address names


## [2.5.0] - 2018-04-03

### Added

- Create reply messages
- Create forward messages
- Quote message in a new message
- Add message as attachment to new message

### Fixed

- AlternativeText now always uses CRLF for breaks
- Improved wrapping quoted text in AlternativeText
- Do not use Unicode for links in HTML converted alternative text
- Wrapping in AlternativeText is now multibyte friendly


## [2.4.0] - 2018-03-08

### Added

- MessageBodyCollection, supersedes FormattedMessageFactory
- Create a message from a MessageBodyCollection
- Attach MessageBodyCollection to a message
- Extract MessageBodyCollection from a message
- Allow to use stream context options for a secure connection


## [2.3.0] - 2018-03-05

### Fixed

- Code Style, via PHP CS Fixer and input from @GawainLynch
- Missing number of sent messages when processing queue, via @GawainLynch

### Added

- Support for IMAP protocol
- ImapTransport
- QueueProcessorInterface, via @GawainLynch
- LimitQueueProcessor, via @GawainLynch
- TimeLimitedQueue, via @GawainLynch
- Support for Url Address in ContentID header
- Support for legacy SMTP server, via @GawainLynch
- Support for HELO SMTP command, via @GawainLynch
- ConsoleBackend for SMTP server, via @GawainLynch
- Start of documentation


## [2.2.0] - 2018-01-05

### Added

- SMTP server
- Support for AppVeyor


## [2.1.7] - 2018-01-05

### Fixed

- DKIM: Rewind streams before sending and/or signing to have correct signed messages.


## [2.1.6] - 2018-01-05

### Added

- AggregateTransport to send messages to multiple transports.


## [2.1.5] - 2017-12-05

### Added

- Readable string method to address


## [2.1.4] - 2017-11-07

### Fixed

- Fix resending same message, streams must be rewinded.


## [2.1.3] - 2017-11-07

### Fixed

- Bug when using FormattedMessageFactory with only text


## [2.1.2] - 2017-10-12

### Fixed

- More verbose error error warning when parsing addresses fail.


## [2.1.1] - 2017-09-26

### Fixed

- Encoding issue when comma with  non 7 bit character in address name


## [2.1.0] - 2017-09-25

### Added

- DKIM support
- Convenience constructors for recipients headers
- PHP7.2 Support
