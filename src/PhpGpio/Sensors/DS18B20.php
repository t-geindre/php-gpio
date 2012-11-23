<?php

namespace PhpGpio\Sensors;

class DS18B20 implements SensorInterface
{

    private $rawFile = '/sys/bus/w1/devices/28-000003ced8f4/w1_slave';

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
     * @return float $value
     */
    public function read($args = array())
    {
	$raw = file_get_contents($this->rawFile);
	$raw = str_replace("\n", "", $raw);
	$boom = explode('t=',$raw);
	return floatval($boom[1]/1000);
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

