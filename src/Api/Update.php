<?php

namespace Bobospay\Api;

use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\Util;

/**
 * trait Update
 */
trait Update
{
    /**
     * Static method to update a resource
     * @param string $id The ID of the API resource to update.
     * @param array $params The request params
     * @param array $headers the request headers
     *
     * @return Resource the updated API resource
     * @throws InvalidRequest|ApiConnection
     */
    public static function update($id, $params = [], $headers = [])
    {
        self::_validateParams($params);
        $path = static::resourcePath($id);
        $className = static::className();

        list($response, $opts) = self::getRequestor()->put($path, $headers, $params);

        $object = Util::arrayToBobospayObject($response, $opts);

        return $object->$className;
    }
}
