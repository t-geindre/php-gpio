<?php

namespace PhpGpio\Server\Provider;

use React\Socket\ConnectionInterface;

/**
 * Server provider for gpio access
 */
class GpioProvider extends AbstractProvider
{
    const TOKEN = 'gpio';

    /**
     * {@inheritdoc}
     */
    public function accept($token)
    {
        return strtolower($token) == self::TOKEN;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ConnectionInterface $conn, array $arguments)
    {
        $this->validateArguments([['str', true], ['int', true], ['int', false]], $arguments);

        $cmd = array_shift($arguments);
        if (method_exists($this, $method = sprintf('%sCommand', strtolower($cmd)))) {
            $conn->write(sprintf(
                "%s\n",
                call_user_func_array([$this, $method], $arguments)
            ));
            return;
        }

        throw new \InvalidArgumentException(sprintf('Unknow command "%s"', $cmd));
    }

    public function readCommand($pinId)
    {
        return 1;
    }

    public function writeCommand($pinId, $value)
    {
        return 'DONE';
    }
}
