<?php

namespace PhpGpio;

/**
 * Get PI informations
 */
class Pi
{
    /**
     * Get RaspberryPi version
     *
     * A list of Model and Pi Revision & Hardware Revision Code from '/proc/cpuinfo' is here:
     * @link http://www.raspberrypi-spy.co.uk/2012/09/checking-your-raspberry-pi-board-version/
     *
     * @return decimal Raspi version
     */
    public function getVersion()
    {
        $cpuinfo = preg_split("/\n/", file_get_contents('/proc/cpuinfo'));
        foreach ($cpuinfo as $line) {
            if (preg_match('/Revision\s*:\s*([^\s]*)\s*/', $line, $matches)) {
                return hexdec($matches[1]);
            }
        }

        return 0;
    }

    /**
     * Get CPU load
     *
     * @return array
     */
    public function getCpuLoad()
    {
        return sys_getloadavg();
    }

    /**
     * Get CPU temp
     *
     * @param boolean $fahrenheit
     *
     * @return float
     */
    public function getCpuTemp($fahrenheit = false)
    {
        $cputemp = floatval(file_get_contents('/sys/class/thermal/thermal_zone0/temp'))/1000;

        if ($fahrenheit) {
            $cputemp = 1.8* $cputemp+32;
        }

        return $cputemp;
    }
}
