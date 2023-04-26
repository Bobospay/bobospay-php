<?php

namespace Bobospay\Rest;

use Bobospay\Bobospay;
use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;

/**
 * Class Requestor
 *
 * @package Bobospay
 */
class Requestor
{
    const SANDBOX_BASE = 'https://sandbox.bobospay.com/api';

    const PRODUCTION_BASE = 'https://bobospay.com/api';

    const DEVELOPMENT_BASE = 'http://127.0.0.1:8000/api';

    protected static $httpClient;

    /**
     * @static
     *
     */
    public static function setHttpClient($client)
    {
        self::$httpClient = $client;
    }

    /**
     */
    private function httpClient()
    {
        if (!self::$httpClient) {
            self::$httpClient = HttpClient::instance();
        }

        return self::$httpClient;
    }

    /**#@+
     * @param string $url
     * @param array $headers
     * @param array $queryParameters
     * @see request()
     */
    /**
     * Send a GET request
     * @throws ApiConnection
     * @throws InvalidRequest
     */
    public function get($path, $headers = array(), $queryParameters = array())
    {
        list($url, , $requestParams, $rawHeaders) = $this->getOptions($path, null, $queryParameters, $headers);

        list($rbody, $rcode, $rheaders) = $this->httpClient()->request($url, $rawHeaders, null, HttpClient::GET, $requestParams);

        $options = [
            'apiVersion' => Bobospay::getApiVersion(),
            'environment' => Bobospay::getEnvironment()
        ];

        $response = $this->_interpretResponse($rbody, $rcode, $rheaders);

        return [$response, $options];
    }

    /**
     * Send a DELETE request
     * @param $path
     * @param array $headers
     * @param array $queryParameters
     * @return array
     * @throws ApiConnection
     * @throws InvalidRequest
     */
    public function delete($path, $headers = array(), $queryParameters = array())
    {
        list($url, , $requestParams, $rawHeaders) = $this->getOptions($path, null, $queryParameters, $headers);

        list($rbody, $rcode, $rheaders) = $this->httpClient()->request($url, $rawHeaders, null, HttpClient::DELETE, $requestParams);

        $options = [
            'apiVersion' => Bobospay::getApiVersion(),
            'environment' => Bobospay::getEnvironment()
        ];

        $response = $this->_interpretResponse($rbody, $rcode, $rheaders);

        return [$response, $options];
    }

    /**#@+
     * @param string $url
     * @param array $headers
     * @param array $data
     * @param array $queryParameters
     * @see request()
     */
    /**
     * Send a POST request
     * @throws ApiConnection
     * @throws InvalidRequest
     */
    public function post($path, $headers = array(), $data = array(), $queryParameters = array())
    {
        list($url, $requestData, $requestParams, $rawHeaders) = $this->getOptions($path, $data, $queryParameters, $headers);
        list($rbody, $rcode, $rheaders) = $this->httpClient()->request($url, $rawHeaders, $requestData, HttpClient::POST, $requestParams);

        $options = [
            'apiVersion' => Bobospay::getApiVersion(),
            'environment' => Bobospay::getEnvironment()
        ];

        $response = $this->_interpretResponse($rbody, $rcode, $rheaders);

        return [$response, $options];
    }

    /**#@+
     * @param string $url
     * @param array $headers
     * @param array $data
     * @param array $queryParameters
     * @see request()
     */
    /**
     * Send a POST request
     * @throws ApiConnection
     * @throws InvalidRequest
     */
    public function put($path, $headers = array(), $data = array(), $queryParameters = array())
    {
        list($url, $requestData, $requestParams, $rawHeaders) = $this->getOptions($path, $data, $queryParameters, $headers);

        list($rbody, $rcode, $rheaders) = $this->httpClient()->request($url, $rawHeaders, $requestData, HttpClient::PUT, $requestParams);

        $options = [
            'apiVersion' => Bobospay::getApiVersion(),
            'environment' => Bobospay::getEnvironment()
        ];

        $response = $this->_interpretResponse($rbody, $rcode, $rheaders);

        return [$response, $options];
    }

    /**
     * Return the default request headers
     * @return array
     */
    protected function getOptions($path, $data = null, $queryParameters = null, $headers = null)
    {
        $queryParameters = $queryParameters ?: [];
        $data = $data ?: [];
        $headers = $headers ?: [];

        $headers = array_merge($this->defaultHeaders(), $headers);
        $url = $this->url($path);
        $rawHeaders = [];

        foreach ($headers as $h => $v) {
            $rawHeaders[] = $h . ': ' . $v;
        }

        return [$url, $data, $queryParameters, $rawHeaders];
    }


    /**
     * Format http request error
     * @return void
     * @throws ApiConnection
     */
    protected function handleRequestException($e)
    {
        $message = 'Request error: ' . $e->getMessage();
        $httpStatusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
        $httpRequest = $e->getRequest();
        $httpResponse = $e->getResponse();

        throw new ApiConnection(
            $message,
            $httpStatusCode,
            $httpRequest,
            $httpResponse
        );
    }

    /**
     * Return the default request headers
     * @return array
     */
    protected function defaultHeaders()
    {
        $auth = Bobospay::getClientSecret();

        return [
            'X-Version' => Bobospay::VERSION,
            'X-Api-Version' => Bobospay::getApiVersion(),
            'X-Source' => 'Bobospay PhpLib',
            'X-Bobospay-Client-Id' => Bobospay::getClientId(),
            'Authorization' => "Bearer $auth"
        ];
    }

    /**
     * Return the base url of the requests
     * @return string
     */
    protected function baseUrl()
    {
        $apiBase = Bobospay::getApiBase();
        $environment = Bobospay::getEnvironment();

        if ($apiBase) {
            return $apiBase;
        }

        switch ($environment) {
            case 'sandbox':
            case 'test':
            case null:
                return self::SANDBOX_BASE;
            case 'production':
            case 'live':
                return self::PRODUCTION_BASE;
            case 'development':
            case 'dev':
                return self::DEVELOPMENT_BASE;
        }
    }

    /**
     * Return the request url
     * @return string
     */
    protected function url($path)
    {
        return $this->baseUrl() . '/' . Bobospay::getApiVersion() . $path;
    }

    /**
     * @param string $rbody
     * @param int $rcode
     * @param array $rheaders
     *
     * @return mixed
     * @throws ApiConnection
     */
    private function _interpretResponse($rbody, $rcode, $rheaders)
    {
        $resp = json_decode($rbody, true);
        $jsonError = json_last_error();

        if ($resp === null && $jsonError !== JSON_ERROR_NONE) {
            $msg = "Invalid response body from API: $rbody "
                . "(HTTP response code was $rcode, json_last_error() was $jsonError)";
            throw new ApiConnection($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleErrorResponse($rbody, $rcode, $rheaders, $resp);
        }

        return $resp;
    }

    /**
     * @param string $rbody A JSON string.
     * @param int $rcode
     * @param array $rheaders
     * @param array $resp
     *
     * @throws ApiConnection
     */
    public function handleErrorResponse($rbody, $rcode, $rheaders, $resp)
    {
        $msg = isset($resp['message']) ? $resp['message'] : 'ApiConnection Error';
        throw new ApiConnection($msg, $rcode, $rbody, $resp, $rheaders);
    }
}
