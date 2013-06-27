<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\HttpApiAuth\Tests;

use Che\HttpApiAuth\AuthenticationData;
use Che\HttpApiAuth\AuthenticationScheme;
use Che\HttpApiAuth\HeaderNotFoundException;
use Che\HttpApiAuth\HttpRequest;
use Che\HttpApiAuth\SchemeHandler;
use Che\HttpApiAuth\UnsupportedTokenException;
use Che\HttpApiAuth\WrongSchemeHeaderException;
use Che\HttpApiAuth\WrongHeaderValueException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Test for SchemeHandler
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class SchemeHandlerTest extends TestCase
{
    /**
     * @var SchemeHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->handler = new SchemeHandler('credentials', 'scheme');
    }

    /**
     * @test createCredentialsHeader uses registered scheme for header creation
     */
    public function createCredentialsHeaderDelegation()
    {
        $request = $this->createRequest();
        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->once())
            ->method('createRequestHeaderValue')
            ->with($request, 'user', 'secret')
            ->will($this->returnValue('value'))
        ;

        $header = $this->handler->createCredentialsHeader($request, 'Foo', 'user', 'secret');

        $this->assertEquals('credentials', $header->getName());
        $this->assertEquals('Foo value', $header->getValue());
    }

    /**
     * @test createCredentialsHeader throws exception on unregistered scheme
     */
    public function createCredentialsHeaderWithUnregisteredScheme()
    {
        $request = $this->createRequest();
        try {
            $this->handler->createCredentialsHeader($request, 'Foo', 'user', 'secret');
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test registerScheme uses custom scheme name if provided
     */
    public function registerSchemeWithCustomName()
    {
        $request = $this->createRequest();

        $scheme = $this->createScheme('Foo', 'Bar');

        $scheme
            ->expects($this->once())
            ->method('createRequestHeaderValue')
        ;
        $this->handler->registerScheme($scheme);

        $this->handler->createCredentialsHeader($request, 'Bar', 'user', 'secret');
    }

    /**
     * @test parseRequest get scheme from header and parses value with it
     */
    public function parseRequestDelegation()
    {
        $request = $this->createRequest(['credentials' => 'Foo bar:baz']);
        $token = $this->createToken();

        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->once())
            ->method('parseHeaderValue')
            ->with('bar:baz')
            ->will($this->returnValue($token))
        ;

        $data = $this->handler->parseRequest($request);
        $this->assertEquals($data->getScheme(), 'Foo');
        $this->assertSame($data->getToken(), $token);
    }

    /**
     * @test parseRequest throws exception if credentials header is not found
     */
    public function parseRequestWithoutHeader()
    {
        $request = $this->createRequest(['foo' => 'bar']);

        try {
            $this->handler->parseRequest($request);
        } catch (HeaderNotFoundException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test parseRequest throws exception if credentials header is wrongly formatted
     */
    public function parseRequestWithWrongHeaderFormat()
    {
        $request = $this->createRequest(['credentials' => 'bar']);

        try {
            $this->handler->parseRequest($request);
        } catch (WrongSchemeHeaderException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test parseRequest throws exception if credentials header value is not parsed with scheme
     */
    public function parseRequestWithWrongHeaderValue()
    {
        $request = $this->createRequest(['credentials' => 'Foo bar:baz']);

        $schemeException = new WrongHeaderValueException('bar:baz', 'wrong');
        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->once())
            ->method('parseHeaderValue')
            ->with('bar:baz')
            ->will($this->throwException($schemeException))
        ;

        try {
            $this->handler->parseRequest($request);
        } catch (WrongSchemeHeaderException $e) {
            $this->assertSame($schemeException, $e->getPrevious());
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test parseRequest throws exception if parsed scheme is not registered
     */
    public function parseRequestWithUnregisteredScheme()
    {
        $request = $this->createRequest(['credentials' => 'Foo bar:baz']);

        try {
            $this->handler->parseRequest($request);
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test isRequestValid uses scheme to check request
     */
    public function isRequestValidForScheme()
    {
        $request = $this->createRequest();
        $token = $this->createToken();

        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->once())
            ->method('isRequestValid')
            ->with($request, $token, 'secret')
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($this->handler->isRequestValid($request, new AuthenticationData('Foo', $token), 'secret'));
    }

    /**
     * @test isRequestValid throws exception on unregistered scheme
     */
    public function isRequestValidForUnregisteredScheme()
    {
        $request = $this->createRequest();
        $token = $this->createToken();

        try {
            $this->handler->isRequestValid($request, new AuthenticationData('Foo', $token), 'secret');
        } catch (\OutOfBoundsException $e) {
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test isRequestValid throws exception if token is not supported by scheme
     */
    public function isRequestValidUnsupportedToken()
    {
        $request = $this->createRequest();
        $token = $this->createToken();

        $expectedException = new UnsupportedTokenException($token, 'Wrong');
        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->any())
            ->method('isRequestValid')
            ->will($this->throwException($expectedException))
        ;

        try {
            $this->handler->isRequestValid($request, new AuthenticationData('Foo', $token), 'secret');
        } catch (UnsupportedTokenException $e) {
            $this->assertSame($expectedException, $e);
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /**
     * @test createSchemeHeader uses scheme to generate value
     */
    public function createSchemeHeaderValue()
    {
        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->once())
            ->method('createResponseHeaderValue')
            ->will($this->returnValue('bar:baz'))
        ;

        $header = $this->handler->createSchemeHeader('Foo');
        $this->assertEquals('scheme', $header->getName());
        $this->assertEquals('Foo bar:baz', $header->getValue());
    }

    /**
     * @test createSchemeHeader returns just scheme name if scheme header value is empty
     */
    public function createSchemeHeaderEmptyValue()
    {
        $scheme = $this->createScheme('Foo');
        $scheme
            ->expects($this->once())
            ->method('createResponseHeaderValue')
            ->will($this->returnValue('   '))
        ;

        $header = $this->handler->createSchemeHeader('Foo');
        $this->assertEquals('Foo', $header->getValue());
    }

    /**
     * @test createSchemeHeader throws exception on unregistered scheme
     */
    public function createSchemeHeaderUnregistered()
    {
        try {
            $this->handler->createSchemeHeader('Foo');
        } catch (\OutOfBoundsException $e) {
            return;
        }
    }

    /**
     * Create request mock
     *
     * @param array $headers
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpRequest
     */
    protected function createRequest(array $headers = [])
    {
        $request = $this->getMock('Che\HttpApiAuth\HttpRequest');
        $request
            ->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($headers))
        ;

        return $request;
    }

    /**
     * Create and register scheme mock
     *
     * @param string      $name
     * @param string|null $customName
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticationScheme
     */
    protected function createScheme($name, $customName = null)
    {
        $scheme = $this->getMock('Che\HttpApiAuth\AuthenticationScheme');
        $scheme
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name))
        ;
        $this->handler->registerScheme($scheme, $customName);

        return $scheme;
    }

    protected function createToken()
    {
        return $this->getMock('Che\HttpApiAuth\RequestToken');
    }
}
