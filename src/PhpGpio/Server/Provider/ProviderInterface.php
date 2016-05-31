<?php

namespace PhpGpio\Server\Provider;

use React\Socket\ConnectionInterface;

/**
 * Server provider interface
 */
interface ProviderInterface
{
    /**
     * Return true if given token is accepted by provider
     *
     * @param string $token
     *
     * @return bolean
     */
    public function accept($token);

    /**
     * Execute request
     *
     * @param ConnectionInterface $conn
     * @param array               $arguments
     *
     * @throws \InvalidArgumentException
     *
     * @return boolean TRUE if execution successfull
     */
    public function execute(ConnectionInterface $conn, array $arguments);
}
