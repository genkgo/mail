<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol;

use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\TestMail\AbstractTestCase;

final class CryptoConstantTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_uses_protocol_tls_v12_for_php_71_and_below() {
        $this->assertEquals('tlsv1.2://', CryptoConstant::getDefaultProtocol('7.1.6'));
    }

    /**
     * @test
     */
    public function it_uses_protocol_tls_for_php_72_and_above() {
        $this->assertEquals('tls://', CryptoConstant::getDefaultProtocol('7.2.0'));
    }

    /**
     * @test
     */
    public function it_uses_method_negotiate_in_any_version() {
        $tls012 = STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        $this->assertEquals($tls012, CryptoConstant::getDefaultMethod('7.2.0'));
        $this->assertEquals($tls012, CryptoConstant::getDefaultMethod('7.1.6'));
    }

}