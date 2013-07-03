<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Tests;

use Che\HttpApiAuth\WrongSchemeHeaderException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Test for WrongSchemeHeaderException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class WrongSchemeHeaderExceptionTest extends TestCase
{
    /**
     * @test message without scheme
     */
    public function messageWithoutScheme()
    {
        $e = new WrongSchemeHeaderException('auth', 'Not match');
        $this->assertEquals('Wrong header "auth" format: Not match', $e->getMessage());
    }

    /**
     * @test message without scheme
     */
    public function messageWithScheme()
    {
        $e = new WrongSchemeHeaderException('auth', 'Not match', 'Foo');
        $this->assertEquals('Wrong header "auth" format for scheme "Foo": Not match', $e->getMessage());
    }
}
