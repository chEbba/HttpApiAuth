<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Tests;

use Che\HttpApiAuth\CustomRequest;
use Che\HttpApiAuth\HttpRequest;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Tests for CustomRequest
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class CustomRequestTest extends TestCase
{
    /**
     * @test copy creates CustomRequest from any request implementation
     */
    public function requestCopy()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpRequest $request */
        $request = $this->getMock('Che\HttpApiAuth\HttpRequest');
        $properties = [
            'host' => 'example.com',
            'uri' => '/uri',
            'method' => 'POST',
            'headers' => ['foo' => 'bar'],
            'body' => 'body text'
        ];
        foreach ($properties as $property => $value) {
            $request
                ->expects($this->any())
                ->method('get'.ucfirst($property))
                ->will($this->returnValue($value))
            ;
        }

        $copy = CustomRequest::copy($request);

        $this->assertEquals($request->getHost(), $copy->getHost());
        $this->assertEquals($request->getUri(), $copy->getUri());
        $this->assertEquals($request->getMethod(), $copy->getMethod());
        $this->assertEquals($request->getHeaders(), $copy->getHeaders());
        $this->assertEquals($request->getBody(), $copy->getBody());
    }

    /**
     * @test serialize/deserialize creates copy
     */
    public function serializedCopy()
    {
        $request = new CustomRequest('example.com', '/uri', 'POST', ['foo' => 'bar'], 'body text');

        $copy = unserialize(serialize($request));

        $this->assertEquals($request, $copy);
    }
}
