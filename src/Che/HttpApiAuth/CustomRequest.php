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
 * HTTP Request with custom properties
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class CustomRequest implements HttpRequest, \JsonSerializable, \Serializable
{
    private $method;
    private $uri;
    private $host;
    private $headers;
    private $body;

    /**
     * @param string $host
     * @param string $uri
     * @param string $method
     * @param array  $headers
     * @param string $body
     */
    public function __construct($host, $uri, $method = 'GET', array $headers = [], $body = '')
    {
        $this->host = $host;
        $this->uri = $uri;
        $this->method = strtoupper($method);
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Serialize request properties for JSON as array
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'host' => $this->host,
            'uri' => $this->uri,
            'method' => $this->method,
            'headers' => $this->headers,
            'body' => $this->body
        ];
    }

    /**
     * Serialize request as array of properties
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * Unserialize request from string
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }
}
