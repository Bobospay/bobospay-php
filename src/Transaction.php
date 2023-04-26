<?php

namespace Bobospay;


use Bobospay\Common\BobospayObject;
use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\Util;

/**
 * Class Transaction
 *
 */
class Transaction extends BobospayObject
{
    use Api\All;
    use Api\Retrieve;
    use Api\Create;


    /**
     * @throws InvalidRequest
     * @throws ApiConnection
     */
    public function generateToken($params = [], $headers = [])
    {
        $url = $this->instanceUrl() . '/token';
        list($response, $opts) =  self::getRequestor()->get($url, $headers, $params);
        return Util::arrayToBobospayObject($response, $opts);
    }
}