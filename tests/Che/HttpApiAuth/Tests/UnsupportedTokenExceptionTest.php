<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Tests;

use Che\HttpApiAuth\UnsupportedTokenException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Test for UnsupportedTokenException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class UnsupportedTokenExceptionTest extends TestCase
{
    /**
     * @test getTokenAsString returns string representation of token with class and username
     */
    public function tokenStringWithClassAndUsername()
    {
        $token = $this->getMock('Che\HttpApiAuth\RequestToken', [], [], 'TokenImpl');
        $token
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('user_name'))
        ;

        $e = new UnsupportedTokenException($token, 'error');
        $this->assertEquals('TokenImpl(user_name)', $e->getTokenAsString());
    }
}
