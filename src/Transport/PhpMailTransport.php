<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\Exception\EnvelopeException;
use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class PhpMailTransport implements TransportInterface
{
    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var \Closure
     */
    private $replacedMailMethod;

    /**
     * @param EnvelopeFactory $envelopeFactory
     * @param array $parameters
     */
    public function __construct(EnvelopeFactory $envelopeFactory, array $parameters = [])
    {
        $this->envelopeFactory = $envelopeFactory;
        $this->parameters = $parameters;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $to = $this->extractSingleHeader($message, 'to');
        $subject = $this->extractSingleHeader($message, 'subject');
        $headers = $this->extractHeaders($message);
        $parameters = $this->constructParameters($message);

        if ($this->replacedMailMethod === null) {
            // @codeCoverageIgnoreStart
            \mail(
                $to,
                $subject,
                (string)$message->getBody(),
                $headers,
                $parameters
            );
        // @codeCoverageIgnoreEnd
        } else {
            $callback = $this->replacedMailMethod;
            $callback(
                $to,
                $subject,
                (string)$message->getBody(),
                $headers,
                $parameters
            );
        }
    }

    /**
     * @param MessageInterface $message
     * @param string $name
     * @return string
     */
    private function extractSingleHeader(MessageInterface $message, string $name): string
    {
        $headers = $message->getHeader($name);

        foreach ($headers as $header) {
            return (string)$header->getValue();
        }

        throw new \InvalidArgumentException('Cannot transport message without header ' . $name);
    }

    /**
     * @param MessageInterface $message
     * @return string
     */
    private function extractHeaders(MessageInterface $message): string
    {
        return \implode(
            "\r\n",
            \array_values(
                \array_filter(
                    \array_map(
                        function (array $headers) {
                            return \implode(
                                "\r\n",
                                \array_map(
                                    function (HeaderInterface $header) {
                                        return (string)(new HeaderLine($header));
                                    },
                                    $headers
                                )
                            );
                        },
                        $message
                            ->withoutHeader('to')
                            ->withoutHeader('subject')
                            ->getHeaders()
                    )
                )
            )
        );
    }

    /**
     * @param MessageInterface $message
     * @return string
     * @throws EnvelopeException
     */
    private function constructParameters(MessageInterface $message): string
    {
        $envelop = $this->envelopeFactory->make($message);
        if (\preg_match('/\"/', $envelop->getAddress())) {
            throw new EnvelopeException(
                'Unable to guarantee injection-free envelop'
            );
        }

        return \implode(' ', \array_merge($this->parameters, ['-f' . (string)$envelop]));
    }

    /**
     * @param \Closure $callback
     * @param EnvelopeFactory $envelopeFactory
     * @param array $parameters
     * @return PhpMailTransport
     */
    public static function newReplaceMailMethod(
        \Closure $callback,
        EnvelopeFactory $envelopeFactory,
        array $parameters = []
    ): PhpMailTransport {
        $transport = new self($envelopeFactory, $parameters);
        $transport->replacedMailMethod = $callback;
        return $transport;
    }
}
