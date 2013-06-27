<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth;

/**
 * Scheme data parsed from header
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class AuthorizationData
{
    private $scheme;
    private $token;

    /**
     * @param string       $scheme Scheme name
     * @param RequestToken $token  Request token
     */
    public function __construct($scheme, RequestToken $token)
    {
        $this->scheme = $scheme;
        $this->token = $token;
    }

    /**
     * Get scheme name
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get request token
     *
     * @return RequestToken
     */
    public function getToken()
    {
        return $this->token;
    }
}