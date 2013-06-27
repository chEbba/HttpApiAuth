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
 * Token implementation is not supported by scheme
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class UnsupportedTokenException extends \RuntimeException
{
    private $token;

    /**
     * @param RequestToken    $token
     * @param string          $reason
     * @param \Exception|null $previous
     */
    public function __construct(RequestToken $token, $reason, \Exception $previous = null)
    {
        $this->token = $token;

        parent::__construct(
            sprintf('Token "%s" is not supported by scheme: %s', $this->getTokenAsString(), $reason),
            0,
            $previous
        );
    }

    /**
     * Token
     *
     * @return RequestToken
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Token in string representation with username and class
     *
     * @return string
     */
    public function getTokenAsString()
    {
        return sprintf('%s(%s)', get_class($this->token), $this->token->getUsername());
    }
}