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
 * HTTP Request
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
interface HttpRequest
{
    /**
     * HTTP Method (uppercase: GET, POST, etc)
     *
     * @return string
     */
    public function getMethod();

    /**
     * Request headers
     *
     * @return Array An array of [name => value]
     */
    public function getHeaders();

    /**
     * Request uri
     *
     * @return string Uri, hostname is stripped
     */
    public function getUri();

    /**
     * Host name
     *
     * @return string
     */
    public function getHost();

    /**
     * HTTP message body
     *
     * @return string
     */
    public function getBody();
}