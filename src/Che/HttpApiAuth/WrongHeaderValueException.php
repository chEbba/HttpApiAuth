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
 * Scheme header value is wrong
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class WrongHeaderValueException extends \RuntimeException
{
    private $value;

    /**
     * @param string          $value
     * @param String          $reason
     * @param \Exception|null $previous
     */
    public function __construct($value, $reason, \Exception $previous = null)
    {
        $this->value = $value;

        parent::__construct(sprintf('Value "%s" for auth header is wrong: %s', $value, $reason, 0 , $previous));
    }

    /**
     * Header value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}