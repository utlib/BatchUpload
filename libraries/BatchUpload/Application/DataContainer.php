<?php

/**
 * Data container class for wizard action hooks.
 * @package BatchUpload
 */
class BatchUpload_Application_DataContainer implements JsonSerializable
{
    protected $_data;

    /**
     * Constructor.
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->_data = $data;
    }

    /**
     * Implementation of JsonSerializable.
     * @return array
     */
    public function jsonSerialize()
    {
        return json_encode($this->_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Return whether a value is stored under the provided key.
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Return a stored value.
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->_data[$key];
    }

    /**
     * Store a value.
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Unset a value.
     * @param string $key
     * @return mixed
     */
    public function pop($key)
    {
        $popped = $this->_data[$key];
        unset($popped);
    }

    /**
     * Return the internal data array.
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}
