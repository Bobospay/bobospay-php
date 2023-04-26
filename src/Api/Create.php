<?php

namespace Bobospay\Api;

use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\Util;

/**
 * trait Create
 */
trait Create
{
    /**
     * Static method to create a resources
     * @param array $params
     * @param array $headers
     *
     * @return Resource
     * @throws InvalidRequest|ApiConnection
     */
    public static function create($params = [], $headers = [])
    {
        self::_validateParams($params);
        $path = static::classPath();
        $className = static::className();

        list($response, $opts) = self::getRequestor()->post($path, $headers, $params);

        $object = Util::arrayToBobospayObject($response, $opts);

        return $object->$className;
    }
}
