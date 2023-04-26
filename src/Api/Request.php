<?php

namespace Bobospay\Api;

use Bobospay\Exception\InvalidRequest;

/**
 * trait Request
 */
trait Request
{
    /**
     * Validate request params
     * @param array $params
     * @throws InvalidRequest
     */
    protected static function _validateParams($params = null)
    {
        if ($params && !is_array($params)) {
            $message = 'You must pass an array as the first argument to Bobospay API '
               . 'method calls.  (HINT: an example call to create a customer '
               . "would be: \"Customer::create(array('firstname' => 'John', "
               . "'lastname' => 'Doe', 'email' => 'johndoe@gmail.com', 'phone' => '66666666'))\")";
            throw new InvalidRequest($message);
        }
    }
}
