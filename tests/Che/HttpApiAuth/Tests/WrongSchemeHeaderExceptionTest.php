<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\HttpApiAuth\Tests;

use Che\HttpApiAuth\WrongSchemeHeaderException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Test for WrongSchemeHeaderException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
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
