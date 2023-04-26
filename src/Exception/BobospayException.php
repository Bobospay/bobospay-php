<?php

namespace Bobospay\Exception;


use Exception;

/**
 * Class BobospayException
 *
 */
class BobospayException extends Exception
{
    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var mixed
     */
    private $body;
    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param $message
     * @param $statusCode
     * @param $body
     * @param $errors
     * @param null $headers
     */
    public function __construct($message, $statusCode = null, $body = null, $errors = null, $headers = null)
    {
        parent::__construct($message);

        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->errors = $errors;
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array|null
     */
    public function getHeaders()
    {
        return $this->headers;
    }


}