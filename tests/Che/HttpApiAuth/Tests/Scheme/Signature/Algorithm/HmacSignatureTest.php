<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\HttpApiAuth\Tests\Scheme\Signature\Algorithm;

use Che\HttpApiAuth\Scheme\Signature\Algorithm\HmacSignature;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Test for HmacSignature
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class HmacSignatureTest extends TestCase
{
    /**
     * @test unknown algorithm in constructor should throw an exception
     */
    public function unknownAlgorithm()
    {
        try {
            new HmacSignature('foo');
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test name is a combination of "hmac" and algorithm with replaced ","
     */
    public function nameOfHashAlgorithm()
    {
        $alg = new HmacSignature('tiger128,3');

        $this->assertEquals('hmac-tiger128-3', $alg->getName());
    }

    /**
     * @test sign same as hash_hmac
     */
    public function signWithHmac()
    {
        $alg = new HmacSignature('tiger128,3', false);
        $this->assertEquals(hash_hmac('tiger128,3', 'message', 'secret'), $alg->sign('message', 'secret'));

        $alg = new HmacSignature('tiger128,3', true);
        $this->assertEquals(hash_hmac('tiger128,3', 'message', 'secret', true), $alg->sign('message', 'secret'));
    }
}
