# PHP-GPIO ![maste build](https://api.travis-ci.org/t-geindre/php-gpio.svg?branch=master)

A simple library to read/write Raspberry PI Gpio pins.

# Read/Write Raspberry Pi GPIOs

To read/write Raspberry Pi Gpio, use the `PhpGpio\Gpio` class. It requires an instance of `PhpGpio\Pi` to grab some informations about your raspi.

```php
<?php
namespace MyProject;

use PhpGpio\GpioInterface;

define('PIN_ID', 4);

$pi = new PhpGpio\Pi;
$gpio = new PhpGpio\Gpio($pi);

// Write example
$gpio
    // Setup pin 4 in out mode
    ->setup(PIN_ID, GpioInterface::DIRECTION_OUT)
    // then write 1
    ->output(PIN_ID, GpioInterface::IO_VALUE_ON)
;
sleep(1);

// After 1 second, write 0
$gpio->output(PIN_ID, GpioInterface::IO_VALUE_OFF);
sleep(1);

// Another second later, free the pin
$gpio->unexport(PIN_ID);

// Read example
$value = $gpio
    // Setup pin 4 in out mode
    ->setup(PIN_ID, GpioInterface::DIRECTION_IN)
    // then write 1
    ->input(PIN_ID)
;

// Display value
var_dump($value);

// Then free the pin
$gpio->unexport(PIN_ID);
```

Reading or writing informations on Raspberry Pi GPIOs requires root permissions.
