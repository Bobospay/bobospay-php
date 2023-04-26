<?php

namespace Bobospay\Exception;

/**
 * Class SignatureVerification
 *
 * @package Bobospay\Exception
 */
class SignatureVerification extends BobospayException
{
    private $sigHeader;

    public function __construct(
        $message,
        $sigHeader,
        $body = null
    ) {
        parent::__construct($message, null, $body);
        $this->sigHeader = $sigHeader;
    }

    public function getSigHeader()
    {
        return $this->sigHeader;
    }
}
