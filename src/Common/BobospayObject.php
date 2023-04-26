<?php

namespace Bobospay\Common;

use ArrayAccess;
use Bobospay\Api\Request;
use Bobospay\Exception\InvalidRequest;
use Bobospay\Rest\Requestor;
use Bobospay\Util\Inflector;
use Bobospay\Util\Util;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @property int $id
 */
class BobospayObject implements ArrayAccess, JsonSerializable
{
    use Request;

    /**
     * @see Requestor
     */
    protected static $requestor;

    protected $_values;

    public function __construct($id = null, $opts = null)
    {
        $this->_values = [];

        if (is_array($id)) {
            $this->refreshFrom($id, $opts);
        } elseif ($id !== null) {
            $this->id = $id;
        }
    }

    /**
     * Set requestor
     * @param Requestor $requestor
     */
    public static function setRequestor($requestor)
    {
        self::$requestor = $requestor;
    }

    /**
     * Return the requestor
     * @return Requestor
     */
    public static function getRequestor()
    {
        return self::$requestor ?: new Requestor();
    }

    public static function className()
    {
        $class = get_called_class();
        // Useful for namespaces: Foo\Charge
        if ($postfixNamespaces = strrchr($class, '\\')) {
            $class = substr($postfixNamespaces, 1);
        }

        // Useful for underscored 'namespaces': Foo_Charge
        if ($postfixFakeNamespaces = strrchr($class, '')) {
            $class = $postfixFakeNamespaces;
        }

        if (substr($class, 0, strlen('Bobospay')) == 'Bobospay') {
            $class = substr($class, strlen('Bobospay'));
        }

        $class = str_replace('_', '', $class);
        $name = urlencode($class);
        return strtolower($name);
    }

    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classPath()
    {
        $base = static::className();
        $plurial = Inflector::pluralize($base);

        return "/$plurial";
    }

    /**
     * @return string The instance endpoint URL for the given class.
     * @throws InvalidRequest
     */
    public static function resourcePath($id)
    {
        if ($id === null) {
            $class = get_called_class();
            $message = 'Could not determine which URL to request: '
                . "$class instance has invalid ID: $id";
            throw new InvalidRequest($message, null);
        }

        $base = static::classPath();
        $extn = urlencode($id);

        return "$base/$extn";
    }

    /**
     * @return string The full API URL for this API resource.
     * @throws InvalidRequest
     */
    public function instanceUrl()
    {
        return static::resourcePath($this['id']);
    }

    // Standard accessor magic methods
    public function __set($k, $v)
    {
        if ($v === '') {
            throw new InvalidArgumentException(
                'You cannot set \'' . $k . '\'to an empty string. '
                . 'We interpret empty strings as NULL in requests. '
                . 'You may set obj->' . $k . ' = NULL to delete the property'
            );
        }

        $this->_values[$k] = $v;
    }

    public function &__get($k)
    {
        // function should return a reference, using $nullval to return a reference to null
        $nullval = null;
        if (!empty($this->_values) && array_key_exists($k, $this->_values)) {
            return $this->_values[$k];
        } else {
            $class = get_class($this);
            error_log("Bobospay Notice: Undefined property of $class instance: $k");
            return $nullval;
        }
    }

    public function __isset($k)
    {
        return isset($this->_values[$k]);
    }

    public function __unset($k)
    {
        unset($this->_values[$k]);
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return array_key_exists($offset, $this->_values);
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return array_key_exists($offset, $this->_values) ? $this->_values[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->$offset);
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return $this->_values;
    }

    public function __toJSON()
    {
        return json_encode($this->__toArray(true), JSON_PRETTY_PRINT);
    }

    public function __toString()
    {
        $class = get_class($this);
        return $class . ' JSON: ' . $this->__toJSON();
    }

    public function __toArray($recursive = false)
    {
        if ($recursive) {
            return Util::convertBobospayObjectToArray($this->_values);
        } else {
            return $this->_values;
        }
    }

    public function serializeParameters()
    {
        $params = [];

        foreach ($this->_values as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if ($value instanceof BobospayObject) {
                $serialized = $value->serializeParameters();
                if ($serialized) {
                    $params[$key] = $serialized;
                }
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    public function refreshFrom($values, $opts)
    {
        if (!is_null($values)) {
            if ($values instanceof BobospayObject) {
                $values = $values->__toArray(true);
            }

            foreach ($values as $k => $value) {
                if (is_array($value)) {
                    $k = Util::stripApiVersion($k, $opts);
                    $this->_values[$k] = Util::arrayToBobospayObject($value, $opts);
                } else {
                    $this->_values[$k] = $value;
                }
            }
        }
    }

    public function keys()
    {
        return array_keys($this->_values);
    }

    // Magic method for var_dump output. Only works with PHP >= 5.6
    public function __debugInfo()
    {
        return $this->_values;
    }
}
