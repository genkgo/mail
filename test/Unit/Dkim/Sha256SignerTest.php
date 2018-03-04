<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\Sha256Signer;
use Genkgo\TestMail\AbstractTestCase;

final class Sha256SignerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_loads_from_file()
    {
        $this->assertInstanceOf(
            Sha256Signer::class,
            Sha256Signer::fromFile(__DIR__ . '/../../Stub/Dkim/dkim.test.priv')
        );
    }

    /**
     * @test
     */
    public function it_throws_on_invalid_key()
    {
        $this->expectException(\InvalidArgumentException::class);
        Sha256Signer::fromString('i_do_not_exist.key');
    }

    /**
     * @test
     */
    public function it_throws_on_invalid_file()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File does not exist');
        Sha256Signer::fromFile('i_do_not_exist.key');
    }

    /**
     * @test
     */
    public function it_throws_on_invalid_constructor_argument()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Sha256Signer(\fopen('data://text/plain;base64,SSBsb3ZlIFBIUAo=', 'r+'));
    }

    /**
     * @test
     */
    public function it_throws_on_encrypted_key()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create resource from private key string');

        Sha256Signer::fromFile(__DIR__ . '/../../Stub/Dkim/dkim.test.protected.priv');
    }

    /**
     * @test
     */
    public function it_signs_with_key()
    {
        $body = 'test-body';
        $header = 'test-header';
        $signer = Sha256Signer::fromFile(__DIR__ . '/../../Stub/Dkim/dkim.test.priv');
        $bodyHash = $signer->hashBody($body);
        $headerHash = $signer->signHeaders($header);

        $this->assertEquals(\hash('sha256', $body, true), $bodyHash);
        $this->assertEquals(
            1,
            \openssl_verify(
                $header,
                $headerHash,
                \file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.pub'),
                OPENSSL_ALGO_SHA256
            )
        );
    }

    /**
     * @test
     */
    public function it_signs_with_protected_key()
    {
        $body = 'test-body';
        $header = 'test-header';
        $signer = Sha256Signer::fromFile(
            __DIR__ . '/../../Stub/Dkim/dkim.test.protected.priv',
            'test'
        );

        $bodyHash =$signer->hashBody($body);
        $headerHash = $signer->signHeaders($header);

        $this->assertEquals(\hash('sha256', $body, true), $bodyHash);
        $this->assertEquals(
            1,
            \openssl_verify(
                $header,
                $headerHash,
                \file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.protected.pub'),
                OPENSSL_ALGO_SHA256
            )
        );
    }
}
