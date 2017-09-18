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
    private $testPrivKey = '-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA23d4ycdKEMp3KH1yn37eYODQYZ/Qk1p0qu4uRpr4B04Gu7Lw
1uSuTjVmiiDkWAWub+vVT+7Ve+03kjSNmDfebpKY4jXSw1ka4nTaGcTeRRoPWsvN
1neT3pwC14B0uv5CptwhkFbEV5X89rSIRgc6AjEK7d70sa3RQHgGSKhCjDp4nEm9
Dv8VnQt2hxsOHWTtu2Vcyw492kaqnHoXe81WxB+d9SwwDoAZQmIqGBFdI7kVRl/V
+z3C9Akd0W5aVus4L6cQyofnttI0svjzInv3gVs4H3VauI+oaYnH03fiGy6AqLst
om9B0mm9fVpSAg+s1AbbcUzMdDyqA15bDlNlAwIDAQABAoIBAEJO+Y61iNpD4fa4
2F36PgQ1SKCGYcVzqhZO+mpYviGu4Hfrm7rBwyxcFAwd3f/+T3L/ZSbOeXAE/ypM
eI+KKclsv4ZxTqm5DVdoiNEKW0GzmvoK47ktzd6PcohcBmjNE6RIlFeA77eq2JBN
gXLvEgbBfJTcLUBVzQhWe0eOlvS40oVgmq6pYVXSJdLLnN5tegSboKGEzo2IhMTD
RsHzfPkWlk3YU6tKfJM6UIkIc9MHfIeK8mcUA1huMyM3cGk/tB3sLlu9ZXBcsHYd
ho3CGLi+gCWspoQEDl+kLGCqcUuYeq7oBNi+6rIFkSFFEsPp9PNAEStTexROF59X
BjPGICECgYEA91apyjf1/oiKoxyjjMPoMAJ4J0hF/+sgGZjdHCsciabQh1JwpB22
FBpw/PQyOT3/mrO+xA3TNgLHean7dVB3P21cOyLydDD3qPC30sgGBZ+S5NGR4oHS
lqupgnz3ppAcVn3ggMTeslC6h/7/8kFh1R8g/Nv93v/fVTnNwt0BgBECgYEA4ybx
ilw2blHodBgAl4wpvxQrKPSMbuCrbiEGeQEmhHlT594AlDT5B8jDS6+eEn07MpKi
zZy11BvctoHj3rG3nBDAnGG7yElBaJHPmrGDhtJroOMClhvxaY6TdiiUqQxfif1P
ecznGWQtiGGVjEdwGOCSFiHsurMQ9nEFaSKQZ9MCgYEAxOzQJHvntK9bykBcCxBT
hh4BMi195iN7HEY0DWBZyVLyhjtiCZjKRjlDKnL2pdKx9qcTxJ7JQiB2V2y6E45s
UyisHT1W0qHGHVEC2qR8/u8tEle1EiWQ2Ht2a7k5p/jnRwnTvFKCiHB0AyFJAMWD
sh6lsg0plOoeE4oBRBuYPTECgYBCcYfBswtw5aCbJNI3ghZMADhHuJDDdhBvHFXq
Wz3LDjpO3o9Iyt31OvJ1Vx9jxSHlvyLEBgzhyGydLg1bfJx6mCPfGm91PIhXcB9L
3pTcgPxeiUieY/oPqFbV/zTM5gOkN2Zh+F+4+6ad9/1olRTjEf1pX+8BBZP2okS0
5hlbZQKBgQDuLeCNnpX6B3Upqs6mzn9TRD8xLhRBuFPwS6F00Qj4LV4gvNzl8SGS
W68zU/XA4slBjRN7Emty5Xz3e1z5DYIA76Gl1qk+LpXKrP3zmN8c5VyGx/DoMY3W
tIfbGi7F3NgqxEjn5qmp/kCPju+SfUHx3jy2A5IodYAkWMzDh/jWYQ==
-----END RSA PRIVATE KEY-----';
    /**
     * @var string
     */
    private $testPubKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA23d4ycdKEMp3KH1yn37e
YODQYZ/Qk1p0qu4uRpr4B04Gu7Lw1uSuTjVmiiDkWAWub+vVT+7Ve+03kjSNmDfe
bpKY4jXSw1ka4nTaGcTeRRoPWsvN1neT3pwC14B0uv5CptwhkFbEV5X89rSIRgc6
AjEK7d70sa3RQHgGSKhCjDp4nEm9Dv8VnQt2hxsOHWTtu2Vcyw492kaqnHoXe81W
xB+d9SwwDoAZQmIqGBFdI7kVRl/V+z3C9Akd0W5aVus4L6cQyofnttI0svjzInv3
gVs4H3VauI+oaYnH03fiGy6AqLstom9B0mm9fVpSAg+s1AbbcUzMdDyqA15bDlNl
AwIDAQAB
-----END PUBLIC KEY-----
';
    /**
     * @var string
     */
    private $testPrivKeyProtected = '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,F0166D58B5D5DB7D

05OT4uVpTYjRYB4MQyCHS+pylnmMZ7h1Jc/f0c7gNFY1a528rMaSZXKxImDWWFgu
FMX1wkq2qEeQ3UE1gS76XAuUmeiCsD2nx173EIodidzzi1tnSu8w38eg5tihtmER
kMlt9cLfFJ+8AmZJKiWiTx7bRKGmg68A6Cs+zzDS74cvZ9WzjOVZGNIQzPQP9XvD
9Oth5Njvjo96TMz9C24W4zgTs8+KzSXPLqDjY3y2RjnbbseKuStg8a7vZkV+h4RB
rMq7QQNtD53Io9X3E35LmHWiSlbZFF9X40F8gm+wJrmHIJrfrQ+PwOWjVfoNNNYZ
Op4y252aV6RUizGZ+rey626NQ9HLmCxwhAipshxYSK2X6cNojrZrxDg0o7x4eYDi
In4mcOF2xnAUJbEP3Sejo7ZNQU23L4ZVZUUyXhOcVOm49q6/WX186lqRFT/Ohjfd
9kIWNZ3azTxTzZuHBSKkyBCNsWp1AHoxDrsyg/SbREEaUwTr4tUBP6ckWIf7upZC
tRQDg3eIHgLkP1Avi+8FAA/T+xAy3XnYgL6pig8nA3qd6lsBS99Lhmgf6Ov0A17W
xO7SUdf0rFM4ZDkIEMGdcyFQ60+aSQ/SIv6XKcKLPM10zRQEAaH/xa/nZE+pmX2j
heUtPR8BWQtE6KdHl4b6cqzeJskaJBBn9rDWfFIkorDyprc437F6+loZOh1xxjMZ
JipLj/oTBE9Hb2nk5febNHF5z314rQQCF3MJKAtrDphGH2NOpOBVDl3K7cN+209i
2BSgX7ugGl2v05vCGFQzjRp3UW0oT6UPq119mblz+3Vf1li12E68OTcdU4m+MXcs
1fTtrJ78+JuteEYZkY+2qrxUQWVhbSMzyZnQU7aUH73ZMkog6rV76LTIEVIGz2+V
ssLLN6Mr92JpHCdtcitSG2j4oDPPP8NyR+fMyE31RjGU1makC5hIN5L1voBLbhFE
sVVIQsK0dgHZasRdNejZ4UwAietT4ACI5qK77ErBpqSZ28cuTNsq8zuyxi7rKr5U
HZE3NbG12+jDdiWnVKXSBxj8O8IsQ1gfFPy54okBopnIhhj9IVggZdPlSnWXBeaj
JUrWV/B0lhwJhUhWhy0mnfGiZ4dr460Bi7WvapmBYrePZ/df3Q9+iVpDUf7/JeR7
Vatxq/Q/OJv8Z0+ntnTq/0fNsaScKQv4LC7K+ODLUy9VkcUhHECEhBcRiAs75F9+
tmAMs/Mzzc7vNk1zU52DoZx7rzZmna8plf4WDZpy5MHtg/hJe1kgB3rTBRs0JwHj
0p9Ksi1UFMto2DyGx8QLP7orKQNWl/wjXoE71mpj2akYynzr2wZVlIbD3v1g+Ir9
iT/bF9y698J5lpCHCdkNlmWSvVpvHei6/bdq9PTMiXkoN4895/MEwv10Ppf02Ep6
Uw55pcUZFKKZyQ33YtNN0w3Ko+p5jF4nmQO7ZY3M+h8ctPxCVT7GBbCXorVHUqpX
Cwr1btOLSCXv+icgB6k2lkLkTOIbQ0s1wXF4pk9uRtJ3/RqlwBO+BEr47kjoGvzy
QJ6SsDGU7vsc+NIA2drgpXvk7O/yrs/jkxEQyGEDu2hNa6SHakB6DAwAO3z865Kl
-----END RSA PRIVATE KEY-----
';
    /**
     * @var string
     */
    private $testPubKeyProtected = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3HD8OkCt+SjQbZgk7S+C
ahgTobl65Ig6K+e7ETdDh3+ZJBDrxXjW0slcwYYnl5zz5XnklWY7BsqcIztxYt6R
l2qKm5Rhv+LA+3sIpOKNhx2HwEjKsg6iu9bXOavVk4dtw2P+DSmzxkFR9nBnh1T7
6pBC7tcRMrzSIGQtTtFYt755idarUHQpNG//8diWfTavkuVgat3hlzL70zQtGO6H
s1PPWe7Ju94o3gwX8vbOqZg+KzvD7fSBjkNJSXjRGkbZi4o+K+RnC0s1zmVQF1dV
LjAJ7ruyDgSkTSGv11rIn3wpZNucuMmyn8FXPNz1zo78/uUnMz+bPfYxE++wknio
zwIDAQAB
-----END PUBLIC KEY-----
';

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