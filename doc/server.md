# PHP-GPIO Server

Root permission are required to read/write Raspberry PI GPIOs, but running a webserver with the root permissions is __always__ a bad idea.

That's why this library also provide a simple TCP server wich can be used to access GPIOs.

## Creating a server

The server is made with the [reactphp/socket](https://github.com/reactphp/socket) library, so you'll have to instanciate some reactphp objects to be able to run the GPIOs server.

Here is a simple example:

```php
<?php

$loop = React\EventLoop\Factory::create();
$server = new PhpGpio\Server\Server($loop);

// Add the GPIO provider
// If you do not add any providers, the server doesn't know to do anything
$gpioProvider = new PhpGpio\Server\Provider\GpioProvider();
$server->addProvider($gpioProvider);

// As the server do not provide any authentication system,
// you should always bind it on local interface to disallow remote connection
$server->listen(8090, '127.0.0.1');

$loop->run();
```

This example is also available in `doc\examples\simple-server.php`.

## Running server

Now you can run your simple GPIOs server as root user (or with the `sudo` command):

```bash
# php simple-server.php
```

__Remember:__ your server should always be bind on local interface and never being accessible remotely!

## Communication with server

Any TCP client can communicate with a running server. For instance, you can use the `telnet` command :

```bash
$ telnet localhost 8090
```

A special command close the connection: `QUIT`.

Every other commands sent to the server start with a simple token wich determines wich provider will be used to handle request. For instance, for the GPIO provider, every commands starts with `GPIO`.
Then, each provider has it's own communication protocol.

At the moment, only the GPIO provider is available:

### GPIO

Two commands available:
 * `READ PIN-ID`
 * `WRITE PIN-ID VALUE`

Telnet session example:

```bash
$ telnet localhost 8090
Trying 127.0.0.1...
Connected to localhost.
Escape character is '^]'.
>> PHP-GPIO SEVER 1.0
<< GPIO READ 1
>> 1
<< GPIO WRITE 2 1
>> DONE
<< QUIT
Connection closed by foreign host.
```

## Writing your own provider

