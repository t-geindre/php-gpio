<?php

namespace PhpGpio\Sensors;


/*
 * 1-Wire is a device communications bus system designed by Dallas Semiconductor Corp.
 * that provides low-speed data, signaling, and power over a single signal.
 * 1-Wire is similar in concept to I²C, but with lower data rates and longer range.
 * It is typically used to communicate with small inexpensive devices
 * such as digital thermometers and weather instruments.
 * (source : http://en.wikipedia.org/wiki/1-Wire)
*/
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
     * @param  array $args
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
