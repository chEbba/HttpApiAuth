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
 * Parsed username and credentials from request header
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
interface RequestToken
{
    /**
     * User name
     *
     * @return string
     */
    public function getUsername();

    /**
     * User credentials
     *
     * @return string
     */
    public function getCredentials();
}