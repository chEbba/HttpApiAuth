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
 * Scheme header has wrong format
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class WrongSchemeHeaderException extends HeaderException
{
    private $scheme;

    /**
     * @param string          $header
     * @param string          $reason
     * @param string|null     $scheme
     * @param \Exception|null $previous
     */
    public function __construct($header, $reason, $scheme = null, \Exception $previous = null)
    {
        $this->scheme = $scheme;

        parent::__construct(
            $header,
            sprintf(
                'Wrong header "%s" format%s: %s',
                $header,
                $scheme ? sprintf(' for scheme "%s"', $scheme) : '',
                $reason
            ),
            $previous
        );
    }

    /**
     * Scheme name
     *
     * @return string|null
     */
    public function getScheme()
    {
        return $this->scheme;
    }
}