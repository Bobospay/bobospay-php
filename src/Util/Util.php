<?php

namespace Bobospay\Util;

use Bobospay\Common\BobospayObject;
use DateTime;
use InvalidArgumentException;

/**
 * Class Util
 *
 * @package Bobospay\Util
 */
abstract class Util
{
    private static $isHashEqualsAvailable = null;

    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     *
     * @param array|mixed $array
     * @return boolean True if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }

        if ($array === []) {
            return true;
        }

        if (array_keys($array) !== range(0, count($array) - 1)) {
            return false;
        }

        return true;
    }

    /**
     * Strip api version from key
     * @param string $key
     * @param array $opts
     *
     * @return string
     */
    public static function stripApiVersion($key, $opts)
    {
        $apiPart = '';
        if (is_array($opts) && isset($opts['apiVersion'])) {
            $apiPart = $opts['apiVersion'] . '/';
        }

        return str_replace($apiPart, '', $key);
    }

    /**
     * Convert a a response to Bobospay object
     * @param array $resp The response object
     * @param array $opts Additional options.
     *
     * @return array
     */
    public static function convertToBobospayObject($resp, $opts)
    {
        $types = [
            'v1/currency' => 'Bobospay\\Currency',
            'v1/customer' => 'Bobospay\\Customer',
            'v1/transaction' => 'Bobospay\\Transaction',
        ];

        if (self::isList($resp)) {
            $mapped = [];
            foreach ($resp as $i) {
                $mapped[] = self::arrayToBobospayObject($i, $opts);
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['klass']) && is_string($resp['klass']) && isset($types[$resp['klass']])) {
                $class = $types[$resp['klass']];
            } else {
                $class = BobospayObject::class;
            }
            $object = new $class;
            $object->refreshFrom($resp, $opts);
            return $object;
        } else {
            return $resp;
        }
    }

    /**
     * Converts an array to Bobospay object.
     *
     * @param array $array The PHP array to convert.
     * @param array $opts Additional options.
     *
     * @return array|BobospayObject
     */
    public static function arrayToBobospayObject($array, $opts)
    {
        if (self::isList($array)) {
            $mapped = array();
            foreach ($array as $i) {
                $mapped[] = self::convertToBobospayObject($i, $opts);
            }

            return $mapped;
        } else {
            return self::convertToBobospayObject($array, $opts);
        }
    }

    /**
     * Recursively converts the PHP Bobospay object to an array.
     *
     * @param array $values The PHP Bobospay object to convert.
     * @return array
     */
    public static function convertBobospayObjectToArray($values)
    {
        $results = [];

        foreach ($values as $k => $v) {
            if ($v instanceof BobospayObject) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertBobospayObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }

        return $results;
    }


    /**
     * Check a date value
     * @param mixed $date
     * @return int|string
     */
    public static function toDateString($date)
    {
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d H:i:s');
        } else if (is_string($date) || is_int($date)) {
            return $date;
        } else {
            throw new InvalidArgumentException(
                'Invalid datetime argument. Should be a date in string format, '
                . ' a timestamp  or an instance of \DateTime.'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public static function encodeParameters($params)
    {
        $flattenedParams = [];
        self::flattenParams($params, $flattenedParams);

        $pieces = [];
        foreach ($flattenedParams as $param) {
            list($k, $v) = $param;
            $pieces[] = self::urlEncode($k) . '=' . self::urlEncode($v);
        }

        return implode('&', $pieces);
    }

    /**
     * Flattens the array so that it can be used with curl.
     *
     * @param $arrays
     * @param array $new
     * @param null $prefix
     */
    public static function flattenParams($arrays, &$new = array(), $prefix = null)
    {
        $isList = self::isList($arrays);

        foreach ($arrays as $key => $value) {
            if (isset($prefix) && $isList) {
                $k = $prefix . '[]';
            } elseif (isset($prefix)) {
                $k = $prefix . '[' . $key . ']';
            } else {
                $k = $key;
            }

            if (is_array($value)) {
                self::flattenParams($value, $new, $k);
            } else {
                $new[] = [$k, $value];
            }
        }
    }

    /**
     * @param string $key A string to URL-encode.
     *
     * @return string The URL-encoded string.
     */
    public static function urlEncode($key)
    {
        $s = urlencode($key);
        // Don't use strict form encoding by changing the square bracket control
        // characters back to their literals. This is fine by the server, and
        // makes these parameter strings easier to read.
        $s = str_replace('%5B', '[', $s);
        return str_replace('%5D', ']', $s);
    }

    /**
     * Compares two strings for equality. The time taken is independent of the
     * number of characters that match.
     *
     * @param string $a one of the strings to compare.
     * @param string $b the other string to compare.
     * @return bool true if the strings are equal, false otherwise.
     */
    public static function secureCompare($a, $b)
    {
        if (self::$isHashEqualsAvailable === null) {
            self::$isHashEqualsAvailable = function_exists('hash_equals');
        }

        if (self::$isHashEqualsAvailable) {
            return hash_equals($a, $b);
        } else {
            if (strlen($a) != strlen($b)) {
                return false;
            }

            $result = 0;
            for ($i = 0; $i < strlen($a); $i++) {
                $result |= ord($a[$i]) ^ ord($b[$i]);
            }
            return ($result == 0);
        }
    }
}
