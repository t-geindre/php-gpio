<?php

namespace PhpGpio\Sensors;

class DS18B20 implements SensorInterface
{

    /**
     * Setup
     *
     * @param array $args
     * @return $this
     */
    public function setup($args = array())
    {
        return false;
    }

    /**
     * Read
     *
     * @param array $args
     * @return $value
     */
    public function read($args = array())
    {
        return false;
    }

    /**
     * Write
     *
     * @param array $args
     * @return $this
     */
    public function write($args = array())
    {
        return false;
    }

}

