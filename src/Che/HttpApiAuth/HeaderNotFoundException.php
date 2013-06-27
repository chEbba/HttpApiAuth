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
 * Expected header was not found in request
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class HeaderNotFoundException extends HeaderException
{
    /**
     * @param string          $header
     * @param \Exception|null $previous
     */
    public function __construct($header, \Exception $previous = null)
    {
        parent::__construct($header, sprintf('Expected header "%s" is not found', $header), $previous);
    }
}