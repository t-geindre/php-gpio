<?php
// Before executing examples, run: $ composer install
require __DIR__.'/../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$server = new PhpGpio\Server\Server($loop);
$gpioProvider = new PhpGpio\Server\Provider\GpioProvider();

$server->addProvider($gpioProvider);
$server->listen(8090, '127.0.0.1');
$loop->run();
