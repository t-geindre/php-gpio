<?php

namespace PhpGpio\Server;

use React\Socket\Server as ReactServer;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

use PhpGpio\Server\Provider\ProviderInterface;

class Server extends ReactServer
{
    protected $providers;

    const VERSION = '1.0';

    public function __construct(LoopInterface $loop)
    {
        parent::__construct($loop);

        $this->on('connection', [$this, 'onConnection']);
    }

    public function onConnection(ConnectionInterface $conn)
    {
        $conn->write(sprintf("PHP-GPIO SEVER %s\n", self::VERSION));

        $self = $this;
        $conn->on('data', function($data) use ($conn, $self) {
            $self->onData($conn, $data);
        });
    }

    public function onData(ConnectionInterface $conn, $data)
    {
        $data = explode(' ', str_replace(["\r", "\n"], '', $data));
        $token = array_shift($data);

        if (empty($token)) {
            return;
        }

        if (strtolower($token) === 'QUIT') {
            $conn->close();
            return;
        }

        try {
            foreach ($this->providers as $provider) {
                if ($provider->accept($token)) {
                    $provider->execute($conn, $data);
                    return;
                }
            }
            throw new \InvalidArgumentException(
                sprintf("No provider to manage \"%s\" token", $token)
            );
        } catch (\InvalidArgumentException $e) {
            $conn->write(sprintf("ERROR: %s\n", $e->getMessage()));
        }
    }

    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
