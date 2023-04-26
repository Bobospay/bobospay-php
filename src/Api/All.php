<?php

namespace Bobospay\Api;

use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\Util;

/**
 * trait All
 */
trait All
{
    /**
     * Static method to retrieve a list of resources
     * @param array $params
     * @param array $headers
     *
     * @throws InvalidRequest|ApiConnection
     */
    public static function all($params = [], $headers = [])
    {
        self::_validateParams($params);
        $path = static::classPath();
        list($response, $opts) = self::getRequestor()->get($path, $headers, $params);

        return Util::arrayToBobospayObject($response, $opts);
    }
}
