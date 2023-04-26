<?php

namespace Bobospay\Api;

use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\Util;

/**
 * trait Search
 */
trait Search
{
    /**
     * Static method to search resources
     * @param array $params
     * @param array $headers
     *
     * @return array Bobospay
     * @throws InvalidRequest|ApiConnection
     */
    public static function search($q, $params = [], $headers = [])
    {
        $params['search'] = $q;
        self::_validateParams($params);
        $path = static::resourcePath('search');
        list($response, $opts) = self::getRequestor()->get($path, $headers, $params);

        return Util::arrayToBobospayObject($response, $opts);
    }
}
