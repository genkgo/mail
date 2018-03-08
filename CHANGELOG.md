# Change Log


All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


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