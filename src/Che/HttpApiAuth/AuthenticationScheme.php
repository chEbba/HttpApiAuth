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
 * Authorization scheme handles auth in header
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
interface AuthenticationScheme
{
    /**
     * Get scheme name
     * Will be used by default in headers
     *
     * @return string
     */
    public function getName();

    /**
     * Parse header scheme value for user and credentials
     * Header is Authorization: <SchemeName> <scheme_value>
     *
     * @param string $value
     *
     * @return RequestToken
     * @throws WrongHeaderValueException If header is wrongly formatted
     */
    public function parseHeaderValue($value);

    /**
     * Create response header value
     *
     * @see parseHeaderValue()
     *
     * @param HttpRequest $request
     * @param string      $username
     * @param string      $secretKey
     *
     * @return string
     */
    public function createRequestHeaderValue(HttpRequest $request, $username, $secretKey);

    /**
     * Create header value for client notification about scheme
     * Header is WWW-Authenticate: <SchemeName> <scheme_value>
     *
     * @return string
     */
    public function createResponseHeaderValue();

    /**
     * Check if request is valid with parsed request token and user secretKey
     *
     * @param HttpRequest  $request
     * @param RequestToken $token
     * @param string       $secretKey
     *
     * @return bool
     * @throws UnsupportedTokenException If token is not supported by this scheme
     */
    public function isRequestValid(HttpRequest $request, RequestToken $token, $secretKey);
}