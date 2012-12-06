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

    private $bus = null; // ex: '/sys/bus/w1/devices/28-000003ced8f4/w1_slave'

    /**
     *  Get-Accesssor
     */
    public function getBus()
    {
        return $this->bus;
    }

    /**
     *  Set-Accesssor
     */
    public function setBus($value)
    {
        $this->bus = $value;
    }

    /**
     * Setup
     *
     * @param array $args
     * @return $this
     */
    public function setup($args = array())
    {
        $this->bus = $this->guessBus();
        if(!empty($args['bus']) && file_exists($args['bus'])) {
            $this->bus = $args['bus'];
        }

        return $this;
    }

    /**
     * guessBus: Guess the thermal sensor bus folder path
     *
     * the directory 28-*** indicates the DS18B20 thermal sensor is wired to the bus
     * (28 is the family ID) and the unique ID is a 12-chars numerical digit
     *
     * @return string $busPath
     */
    public function guessBus()
    {
        $busFolders = glob('/sys/bus/w1/devices/28-*'); // predictable path on a Raspberry Pi
        $busPath = false;
        if (count($busFolders)) {
            $busPath = $busFolders[0];
        }

        return $busPath . '/w1_slave';
    }

    /**
     * Read
     *
     * @param  array $args
     * @return float $value
     */
    public function read($args = array())
    {
        if(is_null($this->bus) || !file_exists($this->bus)) {
            throw new \Exception('You have to setup() the sensor (even with empty args) before using the read() method');
        }
        $raw = file_get_contents($this->bus);
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
