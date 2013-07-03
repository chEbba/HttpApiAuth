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
 * Handles header authorization with set of schemes
 *
 * Server:
 *  - Parse authorization header
 *      - Fetch scheme, username and credentials
 *      - External code load secret key for user
 *      - Check that request is valid for username and secret
 *  - If header is not provided, create header for client with auth scheme info
 * Client:
 *  - Create request header for authorization with a specified scheme
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class SchemeHandler
{
    const DEFAULT_CREDENTIALS_HEADER = 'authorization';
    const DEFAULT_SCHEME_HEADER = 'www-authenticate';

    const HEADER_REGEX = '/([a-z0-9]+)\s+(.+)/i';

    private $credentialsHeader;
    private $schemeHeader;
    /** @var AuthenticationScheme[] */
    private $schemes;
    private $defaultScheme = null;

    public function __construct($credentialsHeader = self::DEFAULT_CREDENTIALS_HEADER,
                                $schemeHeader = self::DEFAULT_SCHEME_HEADER)
    {
        $this->schemes = [];
        $this->credentialsHeader = strtolower($credentialsHeader);
        $this->schemeHeader = strtolower($schemeHeader);
    }

    /**
     * Add authentication scheme
     *
     * @param AuthenticationScheme $scheme Scheme instance
     * @param string|null         $name   Custom name. If null, scheme name will be used
     */
    public function registerScheme(AuthenticationScheme $scheme, $name = null)
    {
        $this->schemes[$name ?: $scheme->getName()] = $scheme;
    }

    /**
     * Set default scheme
     *
     * @param string|null $scheme Scheme name or null to clear default
     *
     * @throws \OutOfBoundsException
     */
    public function setDefaultScheme($scheme = null)
    {
        if ($scheme === null) {
            $this->defaultScheme = null;
        } else {
            $this->checkScheme($scheme);
            $this->defaultScheme = $scheme;
        }
    }

    /**
     * Get scheme by name
     *
     * @param string $name
     *
     * @return AuthenticationScheme
     * @throws \OutOfBoundsException
     */
    protected function getScheme($name)
    {
        $this->checkScheme($name);

        return $this->schemes[$name];
    }

    /**
     * Check if scheme registered
     *
     * @param string $name
     *
     * @throws \OutOfBoundsException
     */
    protected function checkScheme($name)
    {
        if (!isset($this->schemes[$name])) {
            throw new \OutOfBoundsException(sprintf('Scheme "%s" is not registered', $name));
        }
    }

    /**
     * Create auth credentials header for client request
     *
     * @param HttpRequest $request   Request for sign
     * @param string      $scheme    Authorization scheme name
     * @param string      $username  Username for API
     * @param string      $secretKey Secret key for API
     *
     * @return HttpHeader
     * @throws \OutOfBoundsException If scheme is not registered
     */
    public function createCredentialsHeader(HttpRequest $request, $scheme, $username, $secretKey)
    {
        $value = $this->getScheme($scheme)->createRequestHeaderValue($request, $username, $secretKey);

        return new HttpHeader($this->credentialsHeader, $scheme . ' ' . $value);
    }

    /**
     * Parse request authentication data
     *
     * @param HttpRequest $request
     *
     * @return AuthenticationData
     * @throws \OutOfBoundsException
     * @throws WrongSchemeHeaderException
     * @throws HeaderNotFoundException
     */
    public function parseRequest(HttpRequest $request)
    {
        foreach ($request->getHeaders() as $name => $value) {
            if ($name === $this->credentialsHeader) {
                if (!preg_match(self::HEADER_REGEX, $value, $matches)) {
                    throw new WrongSchemeHeaderException(
                        $this->credentialsHeader,
                        sprintf('Value does not match "%s"', self::HEADER_REGEX)
                    );
                }

                try {
                    $token = $this->getScheme($matches[1])->parseHeaderValue($matches[2]);
                } catch (WrongHeaderValueException $e) {
                    throw new WrongSchemeHeaderException($this->credentialsHeader, 'Wrong scheme header value', $matches[1], $e);
                }

                return new AuthenticationData($matches[1], $token);
            }
        }

        throw new HeaderNotFoundException($this->credentialsHeader);
    }

    /**
     * Check if request is valid for user
     *
     * @param HttpRequest       $request   Request for check
     * @param AuthenticationData $data      Data parsed from request
     * @param string            $secretKey User API secret key
     *
     * @return bool
     * @throws \OutOfBoundsException
     * @throws UnsupportedTokenException
     */
    public function isRequestValid(HttpRequest $request, AuthenticationData $data, $secretKey)
    {
        return $this->getScheme($data->getScheme())->isRequestValid($request, $data->getToken(), $secretKey);
    }

    /**
     * Create header for client notification about scheme
     *
     * @param string $scheme
     *
     * @return HttpHeader
     * @throws \OutOfBoundsException
     */
    public function createSchemeHeader($scheme)
    {
        $schemeObj = $this->getScheme($scheme);
        $value = $schemeObj->createResponseHeaderValue();

        return new HttpHeader($this->schemeHeader, trim($scheme . ' ' . $value));
    }

    /**
     * Create header for client notification about all supported schemes
     *
     * @return HttpHeader
     */
    public function createMultiSchemeHeader()
    {
        $headers = [];
        foreach ($this->schemes as $name => $scheme) {
            $headers[$name] = $scheme->createResponseHeaderValue();
        }

        return new HttpHeader(
            $this->schemeHeader,
            sprintf('%s %s', implode('|', array_keys($headers)), implode('|', array_values($headers)))
        );
    }

    /**
     * Create scheme header for default scheme, if default scheme is not set multi-scheme header is created
     *
     * @return HttpHeader
     */
    public function createDefaultSchemeHeader()
    {
        return $this->defaultScheme ?
            $this->createSchemeHeader($this->defaultScheme) :
            $this->createMultiSchemeHeader();
    }
}