<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Scheme\Signature\Algorithm;

use Che\HttpApiAuth\Scheme\Signature\SignatureAlgorithm;

/**
 * Signature algorithm based on hmac
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class HmacSignature implements SignatureAlgorithm
{
    private $hashAlgorithm;
    private $binary;

    /**
     * @param string $hashAlgorithm Name of hash algorithm
     * @param bool   $binary        If true result will be a raw binary string, hex string otherwise
     *
     * @see hash_algos()
     */
    public function __construct($hashAlgorithm, $binary = true)
    {
        if (!in_array($hashAlgorithm, self::getHashAlgorithms())) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown hash algorithm "%s". Expected one of ()',
                $hashAlgorithm,
                implode(', ', self::getHashAlgorithms())
            ));
        }
        $this->hashAlgorithm = $hashAlgorithm;
        $this->binary = (bool) $binary;
    }

    /**
     * Get list of available hash algorithms
     *
     * @return array An array of algorithm names
     */
    public static function getHashAlgorithms()
    {
        return hash_algos();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hmac-' . str_replace(',', '-', $this->hashAlgorithm);
    }

    /**
     * {@inheritDoc}
     */
    public function sign($message, $key)
    {
        return hash_hmac($this->hashAlgorithm, $message, $key, $this->binary);
    }
}