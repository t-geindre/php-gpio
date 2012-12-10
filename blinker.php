<?php

# Usage :
#           $ sudo php blinker.php 17

require 'vendor/autoload.php';

use PhpGpio\Gpio;

if (
    'cli' === PHP_SAPI
    && (2 === $argc)
    && (0 < (int)($argv[1]))
)
{
    $pin = (int)$argv[1];
    $gpio = new GPIO();
    if(!in_array($pin, $gpio->getHackablePins())){
        throw new \InvalidArgumentException("$pin is not a hackable gpio pin number");
    }
    $gpio->setup($pin, "out");

    $gpio->output($pin, 1);

    sleep(1);

    $gpio->output($pin, 0);

    $gpio->unexportAll();
}
