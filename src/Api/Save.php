<?php

namespace Bobospay\Api;

use Bobospay\Common\BobospayObject;
use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;

/**
 * trait Save
 */
trait Save
{
    /**
     * Update the resource
     * @param array $headers the request headers
     *
     * @return BobospayObject the updated API resource
     * @throws InvalidRequest|ApiConnection
     */
    public function save($headers = [])
    {
        $params = $this->serializeParameters();
        $className = static::className();
        $path = $this->instanceUrl();

        list($response, $opts) = self::getRequestor()->put($path, $headers, $params);

        $klass = $opts['apiVersion'] . '/' . $className;

        $json = $response[$klass];
        $this->refreshFrom($json, $opts);

        return $this;
    }
}
