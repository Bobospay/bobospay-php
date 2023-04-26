<?php

namespace Bobospay\Api;

use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;

/**
 * trait Update
 */
trait Delete
{
    /**
     * Send a delete request
     * @param array $headers
     * @throws InvalidRequest|ApiConnection
     */
    public function delete($headers = [])
    {
        $path = $this->instanceUrl();
        self::getRequestor()->delete($path, $headers);
        return $this;
    }
}
