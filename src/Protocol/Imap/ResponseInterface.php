<?php

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Exception\AssertionFailedException;

/**
 * Interface ResponseInterface
 * @package Genkgo\Mail\Protocol\Imap
 */
interface ResponseInterface
{

    /**
     * @param CompletionResult $expectedResult
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertCompletion(CompletionResult $expectedResult): ResponseInterface;

    /**
     * @param string $expectedCommand
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertCommand(string $expectedCommand): ResponseInterface;

    /**
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertContinuation(): ResponseInterface;

    /**
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertTagged(): ResponseInterface;

    /**
     * @param string $data
     * @return ResponseInterface
     */
    public function withBody(string $data): ResponseInterface;

    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @return string
     */
    public function __toString(): string;

}