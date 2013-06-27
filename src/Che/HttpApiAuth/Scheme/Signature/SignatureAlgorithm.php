<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Che\HttpApiAuth\Scheme\Signature;

/**
 * Algorithm for message signature
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
interface SignatureAlgorithm
{
    /**
     * Get algorithm name
     * Should be lowercase alphanumeric and can use "-" as separator
     *
     * @return string
     */
    public function getName();

    /**
     * Create message signature
     *
     * @param string $message
     * @param string $key
     *
     * @return string
     */
    public function sign($message, $key);
}