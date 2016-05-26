<?php

namespace PhpGpio\Sensors;

/**
 * Interface implemented by sensors classes.
 */
interface SensorInterface
{
    /**
     * Read
     *
     * @param array $args
     * @return double
     */
    public function read(array $args = []);

    /**
     * Write
     *
     * @param array $args
     * @return $this
     */
    public function write(array $args = []);
}
