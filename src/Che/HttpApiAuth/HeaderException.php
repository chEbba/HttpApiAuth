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
 * Base exception for header errors
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class HeaderException extends \RuntimeException
{
    private $header;

    /**
     * @param string          $header
     * @param string          $message
     * @param \Exception|null $previous
     */
    public function __construct($header, $message, \Exception $previous = null)
    {
        $this->header = $header;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }
}