<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\Sha256Signer;
use Genkgo\Mail\Exception\InvalidPrivateKeyException;
use Genkgo\TestMail\AbstractTestCase;

final class Sha256SignerTest extends AbstractTestCase
{
    /**
     * @var string
     */
    private $testPrivKey;
    /**
     * @var string
     */
    private $testPubKey;
    /**
     * @var string
     */
    private $testPrivKeyProtected;
    /**
     * @var string
     */
    private $testPubKeyProtected;

    public function setUp()
    {
        $this->testPrivKey = file_get_contents(__DIR__ . '/Stub/dkim.test.priv');
        $this->testPubKey = file_get_contents(__DIR__ . '/Stub/dkim.test.pub');
        $this->testPrivKeyProtected = file_get_contents(__DIR__ . '/Stub/dkim.test.protected.priv');
        $this->testPubKeyProtected = file_get_contents(__DIR__ . '/Stub/dkim.test.protected.pub');
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
    public function it_throws_on_encrypted_key()
    {
        $this->expectException(InvalidPrivateKeyException::class);
        $this->expectExceptionMessage('Unable to load DKIM private key');
        new Sha256Signer(
            $this->testPrivKeyProtected
        );
    }

    /**
     * @test
     */
    public function it_signs_with_key()
    {
        $body = 'test-body';
        $header = 'test-header';
        $signer = new Sha256Signer($this->testPrivKey);
        $bodyHash = $signer->hashBody($body);
        $headerHash = $signer->signHeaders($header);

        $handler = hash_init('sha256');
        hash_update($handler, $body);

        $this->assertEquals(hash_final($handler, true), $bodyHash);
        $this->assertEquals(
            1,
            openssl_verify($header, $headerHash, $this->testPubKey, OPENSSL_ALGO_SHA256)
        );
    }

    /**
     * @test
     */
    public function it_signs_with_protected_key()
    {
        $body = 'test-body';
        $header = 'test-header';
        $signer = new Sha256Signer($this->testPrivKeyProtected, 'test');
        $bodyHash =$signer->hashBody($body);
        $headerHash = $signer->signHeaders($header);

        $handler = hash_init('sha256');
        hash_update($handler, $body);

        $this->assertEquals(hash_final($handler, true), $bodyHash);
        $this->assertEquals(
            1,
            openssl_verify($header, $headerHash, $this->testPubKeyProtected, OPENSSL_ALGO_SHA256)
        );
    }
}