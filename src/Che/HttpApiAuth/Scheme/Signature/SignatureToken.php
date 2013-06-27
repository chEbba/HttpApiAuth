<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Scheme\Signature;

use Che\HttpApiAuth\RequestToken;

/**
 * RequestToken for signature scheme
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class SignatureToken implements RequestToken
{
    private $username;
    private $signature;
    private $timestamp;

    /**
     * @param string $username
     * @param int    $timestamp
     * @param string $signature
     */
    public function __construct($username, $timestamp, $signature)
    {
        $this->username = (string) $username;
        $this->timestamp = (int) $timestamp;
        $this->signature = (string) $signature;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return [
            'signature' => $this->signature,
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Request timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Request signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}