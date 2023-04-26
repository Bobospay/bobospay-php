<?php

namespace Bobospay;

class Bobospay
{
    const VERSION = '0.1.1';

    // @var string The Bobospay Client id to be used for requests.
    protected static $clientId = null;

    // @var string The Bobospay Client Secret to be used for requests.
    protected static $clientSecret = null;

    // @var string The Bobospay API base to be used for requests.
    protected static $apiBase = null;

    // @var string|null The Bobospay token to be used for oauth requests.
    protected static $token = null;

    // @var string The environment for the Bobospay API.
    protected static $environment = 'sandbox';

    // @var string The api version.
    protected static $apiVersion = 'v1';

    // @var bool verify ssl certs.
    protected static $verifySslCerts = true;

    // @var string|null the ca bundle path.
    protected static $caBundlePath = null;

    // @var int Maximum number of request retries
    public static $maxNetworkRetries = 0;

    // @var float Maximum delay between retries, in seconds
    private static $maxNetworkRetryDelay = 2.0;

    // @var float Initial delay between retries, in seconds
    private static $initialNetworkRetryDelay = 0.5;

    /**
     * @return null
     */
    public static function getClientId()
    {
        return self::$clientId;
    }

    /**
     * @param null $clientId
     */
    public static function setClientId($clientId)
    {
        self::$clientId = $clientId;
    }

    /**
     * @return null
     */
    public static function getClientSecret()
    {
        return self::$clientSecret;
    }

    /**
     * @param null $clientSecret
     */
    public static function setClientSecret($clientSecret)
    {
        self::$clientSecret = $clientSecret;
    }

    /**
     * @return null
     */
    public static function getApiBase()
    {
        return self::$apiBase;
    }

    /**
     * @param null $apiBase
     */
    public static function setApiBase($apiBase)
    {
        self::$apiBase = $apiBase;
    }

    /**
     * @return null
     */
    public static function getToken()
    {
        return self::$token;
    }

    /**
     * @param null $token
     */
    public static function setToken($token)
    {
        self::$token = $token;
    }

    /**
     * @return string
     */
    public static function getEnvironment()
    {
        return self::$environment;
    }

    /**
     * @param string $environment
     */
    public static function setEnvironment($environment)
    {
        self::$environment = $environment;
    }

    /**
     * @return string
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * @return bool
     */
    public static function isVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    /**
     * @param bool $verifySslCerts
     */
    public static function setVerifySslCerts($verifySslCerts)
    {
        self::$verifySslCerts = $verifySslCerts;
    }

    /**
     * @return null
     */
    public static function getCaBundlePath()
    {
        if (!self::$caBundlePath) {
            self::$caBundlePath = dirname(__FILE__) . '/../cert/ca-certificates.crt';
        }

        return self::$caBundlePath;
    }

    /**
     * @param null $caBundlePath
     */
    public static function setCaBundlePath($caBundlePath)
    {
        self::$caBundlePath = $caBundlePath;
    }

    /**
     * @return int
     */
    public static function getMaxNetworkRetries()
    {
        return self::$maxNetworkRetries;
    }

    /**
     * @param int $maxNetworkRetries
     */
    public static function setMaxNetworkRetries($maxNetworkRetries)
    {
        self::$maxNetworkRetries = $maxNetworkRetries;
    }

    /**
     * @return float
     */
    public static function getMaxNetworkRetryDelay()
    {
        return self::$maxNetworkRetryDelay;
    }

    /**
     * @param float $maxNetworkRetryDelay
     */
    public static function setMaxNetworkRetryDelay($maxNetworkRetryDelay)
    {
        self::$maxNetworkRetryDelay = $maxNetworkRetryDelay;
    }

    /**
     * @return float
     */
    public static function getInitialNetworkRetryDelay()
    {
        return self::$initialNetworkRetryDelay;
    }

    /**
     * @param float $initialNetworkRetryDelay
     */
    public static function setInitialNetworkRetryDelay($initialNetworkRetryDelay)
    {
        self::$initialNetworkRetryDelay = $initialNetworkRetryDelay;
    }

}