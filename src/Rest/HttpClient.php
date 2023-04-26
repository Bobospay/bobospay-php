<?php

namespace Bobospay\Rest;

use Bobospay\Bobospay;
use Bobospay\Exception\ApiConnection;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Util\RandomGenerator;
use Bobospay\Util\Util;
use function define;
use function defined;

// @codingStandardsIgnoreStart
// PSR2 requires all constants be upper case. Sadly, the CURL_SSLVERSION
// constants do not abide by those rules.

// Note the values come from their position in the enums that
// defines them in CURL's source code.

// Available since PHP 5.5.19 and 5.6.3
if (!defined('CURL_SSLVERSION_TLSv1_2')) {
    define('CURL_SSLVERSION_TLSv1_2', 6);
}
// @codingStandardsIgnoreEnd

// Available since PHP 7.0.7 and cURL 7.47.0
if (!defined('CURL_HTTP_VERSION_2TLS')) {
    define('CURL_HTTP_VERSION_2TLS', 4);
}

class HttpClient
{
    /**
     * POST method
     * @var string
     */
    const POST = 'POST';

    /**
     * PUT method
     * @var string
     */
    const PUT = 'PUT';

    /**
     * GET method
     * @var string
     */
    const GET = 'GET';

    /**
     * DELETE method
     * @var string
     */
    const DELETE = 'DELETE';


    /**
     * This is a static class, do not instantiate it
     * @codeCoverageIgnore
     */

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected $defaultOptions;

    protected $userAgentInfo;

    /** @var RandomGenerator */
    protected $randomGenerator;

    // USER DEFINED TIMEOUTS

    const DEFAULT_TIMEOUT = 80;

    const DEFAULT_CONNECT_TIMEOUT = 30;

    private $timeout = self::DEFAULT_TIMEOUT;

    private $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;

    /**
     * CurlClient constructor.
     *
     * Pass in a callable to $defaultOptions that returns an array of CURLOPT_* values to start
     * off a request with, or a flat array with the same format used by curl_setopt_array() to
     * provide a static set of options. Note that many options are overridden later in the request
     * call, including timeouts, which can be set via setTimeout() and setConnectTimeout().
     *
     * Note that request() will silently ignore a non-callable, non-array $defaultOptions, and will
     * throw an exception if $defaultOptions returns a non-array value.
     *
     * @param array|callable|null $defaultOptions
     */
    public function __construct($defaultOptions = null, $randomGenerator = null)
    {
        $this->defaultOptions = $defaultOptions;
        $this->randomGenerator = $randomGenerator ?: new RandomGenerator();
        $this->initUserAgentInfo();
    }

    public function initUserAgentInfo()
    {
        $curlVersion = curl_version();
        $this->userAgentInfo = [
            'httplib' => 'curl ' . $curlVersion['version'],
            'ssllib' => $curlVersion['ssl_version'],
        ];
    }


    /**
     * @throws ApiConnection|InvalidRequest
     */
    public function request($url, $headers, $data, $method, $queryParameters)
    {
        $method = strtolower($method);
        $opts = [];

        if (is_callable($this->defaultOptions)) { // call defaultOptions callback, set options to return value
            $opts = call_user_func_array($this->defaultOptions, func_get_args());
            if (!is_array($opts)) {
                throw new ApiConnection("Non-array value returned by defaultOptions CurlClient callback");
            }
        } elseif (is_array($this->defaultOptions)) { // set default curlopts from array
            $opts = $this->defaultOptions;
        }

        switch ($method) {
            case 'post':
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = Util::encodeParameters($data);
                break;
            case 'put':
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_POSTFIELDS] = Util::encodeParameters($data);
                break;
            case 'get':
                $opts[CURLOPT_HTTPGET] = 1;
                if (count($queryParameters) > 0) {
                    $encoded = Util::encodeParameters($queryParameters);
                    $url = "$url?$encoded";
                }
                break;
            case 'delete':
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                if (count($queryParameters) > 0) {
                    $encoded = Util::encodeParameters($queryParameters);
                    $url = "$url?$encoded";
                }
                break;
            default:
                throw new InvalidRequest("Unrecognized method $method");
        }

        // It is only safe to retry network failures on POST requests if we
        // add an Idempotency-Key header
        if (($method == 'post') && (Bobospay::$maxNetworkRetries > 0)) {
            if (!isset($headers['Idempotency-Key'])) {
                $headers[] = 'Idempotency-Key: ' . $this->randomGenerator->uuid();
            }
        }

        // Create a callback to capture HTTP headers for the response
        $rheaders = [];
        $headerCallback = function ($curl, $header_line) use (&$rheaders) {
            // Ignore the HTTP request line (HTTP/1.1 200 OK)
            if (strpos($header_line, ":") === false) {
                return strlen($header_line);
            }
            list($key, $value) = explode(":", trim($header_line), 2);
            $rheaders[trim($key)] = trim($value);
            return strlen($header_line);
        };

        // By default, for large request body sizes (> 1024 bytes), cURL will
        // send a request without a body and with a `Expect: 100-continue`
        // header, which gives the server a chance to respond with an error
        // status code in cases where one can be determined right away (say
        // on an authentication problem for example), and saves the "large"
        // request body from being ever sent.
        //
        // Unfortunately, the bindings don't currently correctly handle the
        // success case (in which the server sends back a 100 CONTINUE), so
        // we'll error under that condition. To compensate for that problem
        // for the time being, override CURL's behavior by simply always
        // sending an empty `Expect:` header.
        $headers[] = 'Expect: ';

        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
        $opts[CURLOPT_TIMEOUT] = $this->timeout;
        $opts[CURLOPT_HEADERFUNCTION] = $headerCallback;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_CAINFO] = Bobospay::getCABundlePath();
        if (!Bobospay::isVerifySslCerts()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
        }
        // For HTTPS requests, enable HTTP/2, if supported
        $opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2TLS;

        list($rbody, $rcode) = $this->executeRequestWithRetries($opts, $url);

        return [$rbody, $rcode, $rheaders];
    }

    /**
     * @param array $opts cURL options
     * @throws ApiConnection
     */
    private function executeRequestWithRetries($opts, $absUrl)
    {
        $numRetries = 0;

        while (true) {
            $rcode = 0;
            $errno = 0;

            $curl = curl_init();
            curl_setopt_array($curl, $opts);

            $rbody = curl_exec($curl);

            if ($rbody === false) {
                $errno = curl_errno($curl);
                $message = curl_error($curl);
            } else {
                $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            }
            curl_close($curl);

            if ($this->shouldRetry($errno, $rcode, $numRetries)) {
                $numRetries += 1;
                $sleepSeconds = $this->sleepTime($numRetries);
                usleep(intval($sleepSeconds * 1000000));
            } else {
                break;
            }
        }

        if ($rbody === false) {
            $this->handleCurlError($absUrl, $errno, $message, $numRetries);
        }

        return [$rbody, $rcode];
    }

    /**
     * @param string $url
     * @param int $errno
     * @param string $message
     * @param int $numRetries
     * @throws ApiConnection
     */
    private function handleCurlError($url, $errno, $message, $numRetries)
    {
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to Bobospay ($url).  Please check your "
                    . "internet connection and try again.  If this problem persists";
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = "Could not verify Bobospay's SSL certificate.  Please make sure "
                    . "that your network is not intercepting certificates.  "
                    . "(Try going to $url in your browser.)  "
                    . "If this problem persists,";
                break;
            default:
                $msg = "Unexpected error communicating with Bobospay.  "
                    . "If this problem persists,";
        }
        $msg .= " let us know at support@bobospay.com.";

        $msg .= "\n\n(Network error [errno $errno]: $message)";

        if ($numRetries > 0) {
            $msg .= "\n\nRequest was retried $numRetries times.";
        }

        throw new ApiConnection($msg);
    }

    /**
     * Checks if an error is a problem that we should retry on. This includes both
     * socket errors that may represent an intermittent problem and some special
     * HTTP statuses.
     * @param int $errno
     * @param int $rcode
     * @param int $numRetries
     * @return bool
     */
    private function shouldRetry($errno, $rcode, $numRetries)
    {
        if ($numRetries >= Bobospay::getMaxNetworkRetries()) {
            return false;
        }

        // Retry on timeout-related problems (either on open or read).
        if ($errno === CURLE_OPERATION_TIMEOUTED) {
            return true;
        }

        // Destination refused the connection, the connection was reset, or a
        // variety of other connection failures. This could occur from a single
        // saturated server, so retry in case it's intermittent.
        if ($errno === CURLE_COULDNT_CONNECT) {
            return true;
        }

        // 409 conflict
        if ($rcode === 409) {
            return true;
        }

        return false;
    }

    private function sleepTime($numRetries)
    {
        // Apply exponential backoff with $initialNetworkRetryDelay on the
        // number of $numRetries so far as inputs. Do not allow the number to exceed
        // $maxNetworkRetryDelay.
        $sleepSeconds = min(Bobospay::getInitialNetworkRetryDelay() * pow(2, $numRetries - 1), Bobospay::getMaxNetworkRetryDelay());

        // Apply some jitter by randomizing the value in the range of
        // ($sleepSeconds / 2) to ($sleepSeconds).
        $sleepSeconds *= 0.5 * (1 + $this->randomGenerator->randFloat());

        // But never sleep less than the base sleep seconds.
        return max(Bobospay::getInitialNetworkRetryDelay(), $sleepSeconds);
    }


    public function setTimeout($seconds)
    {
        $this->timeout = (int)max($seconds, 0);
        return $this;
    }

    public function setConnectTimeout($seconds)
    {
        $this->connectTimeout = (int)max($seconds, 0);
        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    public function getUserAgentInfo()
    {
        return $this->userAgentInfo;
    }

}