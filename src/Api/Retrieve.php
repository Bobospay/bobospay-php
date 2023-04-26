<?php

namespace Bobospay\Api;


use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\Util;

/**
 * trait Retrieve
 */
trait Retrieve
{
    /**
     * Static method to retrieve a resource
     * @param mixed $id
     * @param array $params
     * @param array $headers
     * @throws InvalidRequest|ApiConnection
     */
    public static function retrieve($id, $params = [], $headers = [])
    {
        $url = static::resourcePath($id);
        $className = static::className();

        list($response, $opts) = self::getRequestor()->get($url,$headers, $params);
        $object = Util::arrayToBobospayObject($response, $opts);

        return $object->$className;
    }
}
