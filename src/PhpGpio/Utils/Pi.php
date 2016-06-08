<?php

namespace PhpGpio\Utils;

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
     * Return the list of avaibles Gpios dependending on your
     * Rapsberry Pi version
     *
     * @return array
     */
    public function getAvailableGpios()
    {
        $version = $this->getVersion();

        if ($version < 4) {
            return [0, 1, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 21, 22, 23, 24, 25];
        }

        if ($version < 16) {
            return [2, 3, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 22, 23, 24, 25, 27];
        }

        return [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
    }
}
