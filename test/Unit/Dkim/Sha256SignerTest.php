<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\Sha256Signer;
use Genkgo\Mail\Exception\InvalidPrivateKeyException;
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
        $this->expectException(InvalidPrivateKeyException::class);
        $this->expectExceptionMessage('Unable to load DKIM private key');
        new Sha256Signer(
            'i_do_not_exist.key'
        );
    }

    /**
     * @test
     */
    public function it_throws_on_invalid_file()
    {
        $this->expectException(InvalidPrivateKeyException::class);
        $this->expectExceptionMessage('File does not exist');
        Sha256Signer::fromFile('i_do_not_exist.key');
    }

    /**
     * @test
     */
    public function it_throws_on_encrypted_key()
    {
        $this->expectException(InvalidPrivateKeyException::class);
        $this->expectExceptionMessage('Unable to load DKIM private key');
        new Sha256Signer(
            file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.protected.priv')
        );
    }

    /**
     * @test
     */
    public function it_signs_with_key()
    {
        $body = 'test-body';
        $header = 'test-header';
        $signer = new Sha256Signer(file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.priv'));
        $bodyHash = $signer->hashBody($body);
        $headerHash = $signer->signHeaders($header);

        $handler = hash_init('sha256');
        hash_update($handler, $body);

        $this->assertEquals(hash_final($handler, true), $bodyHash);
        $this->assertEquals(
            1,
            openssl_verify(
                $header, $headerHash,
                file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.pub'),
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
        $signer = new Sha256Signer(
            file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.protected.priv'),
            'test'
        );
        $bodyHash =$signer->hashBody($body);
        $headerHash = $signer->signHeaders($header);

        $handler = hash_init('sha256');
        hash_update($handler, $body);

        $this->assertEquals(hash_final($handler, true), $bodyHash);
        $this->assertEquals(
            1,
            openssl_verify(
                $header, $headerHash,
                file_get_contents(__DIR__ . '/../../Stub/Dkim/dkim.test.protected.pub'),
                OPENSSL_ALGO_SHA256
            )
        );
    }
}