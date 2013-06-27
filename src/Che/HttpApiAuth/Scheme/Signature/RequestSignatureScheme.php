<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Scheme\Signature;

use Che\HttpApiAuth\AuthenticationScheme;
use Che\HttpApiAuth\HttpRequest;
use Che\HttpApiAuth\RequestToken;
use Che\HttpApiAuth\UnsupportedTokenException;
use Che\HttpApiAuth\WrongHeaderValueException;

/**
 * Auth scheme implementation throw request signature
 * Message for signature is created from method, uri and and body
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class RequestSignatureScheme implements AuthenticationScheme
{
    const PROTOCOL_NAME = 'RequestSignature';
    const HEADER_REGEX = '/^([0-9a-z_@\.\-\=\+]+):([0-9]+):([0-9a-z\=\+]+)$/i';

    private $algorithm;
    private $tokenLifeTime;
    private $encoded;

    /**
     * @param SignatureAlgorithm $algorithm     Algorithm for signature
     * @param bool               $encoded       If true header value is encoded with base64
     * @param int                $tokenLifeTime Number of seconds request is treated as valid,
     *                                          If 0, request is always valid
     */
    public function __construct(SignatureAlgorithm $algorithm, $encoded = true, $tokenLifeTime = 600)
    {
        $this->algorithm = $algorithm;
        $this->encoded = $encoded;
        $this->tokenLifeTime = $tokenLifeTime;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::PROTOCOL_NAME . '-' . strtoupper($this->algorithm->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function parseHeaderValue($value)
    {
        if ($this->encoded) {
            $value = base64_decode($value);
            if ($value === false) {
                throw new WrongHeaderValueException($value, 'Can not decode base64 value');
            }
        }

        if (!preg_match(self::HEADER_REGEX, $value, $matches)) {
            throw new WrongHeaderValueException(
                $value,
                sprintf(
                    'Value should match "%s" pattern%s',
                    self::HEADER_REGEX,
                    $this->encoded ? ' and encoded with base64' : ''
                )
            );
        }

        return new SignatureToken($matches[1], (int) $matches[2], base64_decode($matches[3]));
    }

    /**
     * {@inheritDoc}
     */
    public function createRequestHeaderValue(HttpRequest $request, $username, $secretKey)
    {
        $timestamp = time();
        $value = sprintf(
            '%s:%s:%s',
            $username,
            $timestamp,
            base64_encode($this->signRequest($request, $username, $timestamp, $secretKey))
        );

        if ($this->encoded) {
            $value = base64_encode($value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function createResponseHeaderValue()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function isRequestValid(HttpRequest $request, RequestToken $token, $secretKey)
    {
        if (!$token instanceof SignatureToken) {
            throw new UnsupportedTokenException($token, 'Expected SignatureToken');
        }

        if ($this->tokenLifeTime && (time() - $token->getTimestamp() > $this->tokenLifeTime)) {
            return false; // Token expired
        }

        return $token->getSignature() ===
            $this->signRequest($request, $token->getUsername(), $token->getTimestamp(), $secretKey);
    }

    /**
     * Create request signature
     *
     * @param HttpRequest $request
     * @param string      $username
     * @param string      $timestamp
     * @param string      $secretKey
     *
     * @return string
     */
    protected function signRequest(HttpRequest $request, $username, $timestamp, $secretKey)
    {
        $message = $username
                 . $timestamp
                 . $this->getRequestMessage($request)
        ;

        return $this->algorithm->sign($message, $secretKey);
    }

    /**
     * Create message for request signature
     *
     * @param HttpRequest $request
     *
     * @return string
     */
    protected function getRequestMessage(HttpRequest $request)
    {
        return $request->getMethod()
             . $request->getUri()
             . $request->getBody()
        ;
    }
}