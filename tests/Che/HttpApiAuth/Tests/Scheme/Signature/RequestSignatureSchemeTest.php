<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Tests\Scheme\Signature;

use Che\HttpApiAuth\HttpRequest;
use Che\HttpApiAuth\Scheme\Signature\RequestSignatureScheme;
use Che\HttpApiAuth\Scheme\Signature\SignatureToken;
use Che\HttpApiAuth\UnsupportedTokenException;
use Che\HttpApiAuth\WrongHeaderValueException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Test for RequestSignatureScheme
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class RequestSignatureSchemeTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $algorithm;

    /**
     * Setup Algorithm
     */
    protected function setUp()
    {
        $this->algorithm = $this->getMock('Che\HttpApiAuth\Scheme\Signature\SignatureAlgorithm');
        $this->algorithm
            ->expects($this->any())
            ->method('sign')
            ->with(
                $this->matchesRegularExpression('#user([0-9]+)POST/api\.php\?method=fooparam1=value1&param2=value2#'),
                'secret'
            )
            ->will($this->returnValue('sign'))
        ;
    }

    /**
     * Create SignatureScheme
     *
     * @param bool $encoded
     * @param int  $tokenLifeTime
     *
     * @return RequestSignatureScheme
     */
    private function createScheme($encoded = true, $tokenLifeTime = 600)
    {
        return new RequestSignatureScheme($this->algorithm, $encoded, $tokenLifeTime);
    }

    /**
     * @test getName uses scheme name and signature algorithm
     */
    public function nameWithAlgorithm()
    {
        $this->algorithm
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo-bar'))
        ;

        $this->assertEquals('RequestSignature-FOO-BAR', $this->createScheme()->getName());
    }

    /**
     * @test parseHeaderValue write format is username:lifetime:signature and CAN be encoded with base64
     * @dataProvider headerValues
     */
    public function parseHeaderValueFormat($value, SignatureToken $result = null, $encoded = false)
    {
        $scheme = $this->createScheme($encoded);

        try {
            $token = $scheme->parseHeaderValue($encoded ? base64_encode($value) : $value);
        } catch (WrongHeaderValueException $e) {
            if ($result) {
                $this->fail('Exception should not be thrown');
            }

            return;
        }

        if (!$result) {
            $this->fail('Exception was not thrown');
        }

        $this->assertEquals($result->getUsername(), $token->getUsername());
        $this->assertEquals($result->getTimestamp(), $token->getTimestamp());
        $this->assertEquals($result->getSignature(), $token->getSignature());
    }

    /**
     * @test createRequestHeaderValue generates username:timestamp:signature value with optional base64 encoding
     */
    public function createRequestHeaderValueFormat()
    {
        $request = $this->createRequest();

        $encoded = $this->createScheme();
        $start = time();
        $result = $encoded->createRequestHeaderValue($request, 'user', 'secret');
        $end = time();

        $this->assertHeaderValue('user', base64_encode('sign'), $start, $end, base64_decode($result));

        $encoded = $this->createScheme(false);
        $start = time();
        $result = $encoded->createRequestHeaderValue($request, 'user', 'secret');
        $end = time();

        $this->assertHeaderValue('user', base64_encode('sign'), $start, $end, $result);
    }

    /**
     * @test createResponseHeaderValue return empty string
     */
    public function createResponseHeaderValueEmpty()
    {
        $this->assertEquals('', $this->createScheme()->createResponseHeaderValue());
    }

    /**
     * @test isRequestValid approve checks  signature
     */
    public function isRequestValidRightSignature()
    {
        $request = $this->createRequest();

        $scheme = $this->createScheme(0, 60000);

        $this->assertTrue($scheme->isRequestValid($request, new SignatureToken('user', time(), 'sign'), 'secret'));
        $this->assertFalse($scheme->isRequestValid($request, new SignatureToken('user', time(), 'wrong'), 'secret'));
    }

    /**
     * @test request is not valid if it expires
     */
    public function isRequestExpired()
    {
        $request = $this->createRequest();
        $scheme = $this->createScheme(0, 100);

        $this->assertTrue($scheme->isRequestValid($request, new SignatureToken('user', time() - 50, 'sign'), 'secret'));
        $this->assertFalse($scheme->isRequestValid($request, new SignatureToken('user', time() - 101, 'sign'), 'secret'));
    }

    /**
     * @test risRequestValid throws exception if token is not supported
     */
    public function isRequestUnsupportedToken()
    {
        $request = $this->createRequest();
        $token = $this->getMock('Che\HttpApiAuth\RequestToken');

        try {
            $this->createScheme()->isRequestValid($request, $token, 'secret');
        } catch (UnsupportedTokenException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    public function headerValues()
    {
        $values = [
            ['foo:123:' . base64_encode('baz'), new SignatureToken('foo', 123, 'baz')],
            ['foo-bar@mail-domain.com:123:' . base64_encode('baz123\n\t'), new SignatureToken('foo-bar@mail-domain.com', 123, 'baz123\n\t')],
            ['foo_bar:123:' . base64_encode('baz'), new SignatureToken('foo_bar', 123, 'baz')],
            ['foo:bar:qwe'],
            ['$foo:123:qwe'],
            ['foo:123:$qwe'],
            ['foo:123'],
            ['foo'],
            [''],
            ['foo:123:qwe:asd'],
            ['foo:123:qwe:']
        ];

        $data = [];
        foreach ($values as $value) {
            foreach ([true, false] as $encoded) {
                if (!isset($value[1])) {
                    $value[1] = null;
                }
                $value[2] = $encoded;

                $data[] = $value;
            }
        }

        return $data;
    }

    /**
     * Create request mock
     *
     * @param string $method
     * @param string $uri
     * @param string $body
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpRequest
     */
    private function createRequest($method = 'POST', $uri = '/api.php?method=foo', $body = 'param1=value1&param2=value2')
    {
        $request = $this->getMock('Che\HttpApiAuth\HttpRequest');
        foreach (['method', 'uri', 'body'] as $option) {
            $request
                ->expects($this->any())
                ->method('get'.ucfirst($option))
                ->will($this->returnValue($$option))
            ;
        }

        return $request;
    }

    /**
     * Assert header value is right
     *
     * @param string $username
     * @param string $signature
     * @param int    $start
     * @param int    $end
     * @param string $value
     */
    private function assertHeaderValue($username, $signature, $start, $end, $value)
    {
        $pattern = $username . ':%d:' . $signature;
        $constrains = [];
        foreach (range($start, $end) as $time) {
            $constrains[] = $this->equalTo(sprintf($pattern, $time));
        }
        $constraint = new \PHPUnit_Framework_Constraint_Or();
        $constraint->setConstraints($constrains);

        $this->assertThat($value, $constraint);
    }
}
