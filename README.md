# PHP-GPIO ![master build](https://api.travis-ci.org/t-geindre/php-gpio.svg?branch=master)

A simple library to read/write Raspberry PI GPIOs with PHP.

## Read/Write Raspberry Pi GPIOs

To read/write Raspberry Pi GPIOs, use the `PhpGpio\Gpio` class. The instanciation of this class requires an array of pins numbers you will use.

You can define a specific list of pins numbers, according to your usage and your Rasberry Pi version, or you can use the `PhpGpio\Utils\Pi` class to automaticly find all availables pins:

```php
<?php
namespace myproject;

$pi = new PhpGpio\Utils\Pi;
$pi->getAvailablePins(); // int array
```

Accessing to the GPIOs requires root permissions, so make sure your code is running with enought permissions. __Remember__: you should never run your webserver as root.

Here is a simple example of Gpio class usage:

```php
<?php
namespace MyProject;

use PhpGpio\GpioInterface;
use PhpGpio\Gpio;

// Both pins are available on all raspi versions
define('PIN_IN', 4);
define('PIN_OUT', 7);

$gpio = new Gpio([PIN_IN, PIN_OUT]);

// First, setup pins with correct directions
$gpio
     ->setup(PIN_IN, GpioInterface::DIRECTION_IN) // Makes it readable
     ->setup(PIN_OUT, GpioInterface::DIRECTION_OUT) // Writable
;

// read PIN_IN value and display it
$value = $gpio->read(PIN_IN);
var_dump($value); // string

// write 1 on PIN_OUT
$gpio->write(PIN_OUT, GpioInterface::IO_VALUE_ON);
sleep(1);

// After 1 second, write 0 on PIN_OUT
$gpio->write(PIN_OUT, GpioInterface::IO_VALUE_OFF);

// Then free all pins
// (use the unexport() method to free pins one by one)
$gpio->unexportAll();
```

Check [this page](http://www.raspberrypi-spy.co.uk/2012/06/simple-guide-to-the-rpi-gpio-header-and-pins/) if you need a complete list of availables pins on your Raspberry Pi version.
