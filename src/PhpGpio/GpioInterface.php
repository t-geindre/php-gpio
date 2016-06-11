<?php

namespace PhpGpio;

/**
 * Gpio interface
 *
 * @author Vaidas Lažauskas <vaidas@notrix.lt>
 */
interface GpioInterface
{
    const DIRECTION_IN = 'in';
    const DIRECTION_OUT = 'out';

    const IO_VALUE_ON = 1;
    const IO_VALUE_OFF = 0;

    const PATH_GPIO = '/sys/class/gpio/gpio';
    const PATH_EXPORT = '/sys/class/gpio/export';
    const PATH_UNEXPORT = '/sys/class/gpio/unexport';

    /**
     * Setup pin, takes pin number and direction (in or out)
     *
     * @param int    $pinNo
     * @param string $direction
     *
     * @return GpioInterface
     */
    public function setup($pinNo, $direction);

    /**
     * Get input value
     *
     * @param int $pinNo
     *
     * @return string GPIO value or boolean false
     */
    public function read($pinNo);

    /**
     * Set output value
     *
     * @param int    $pinNo
     * @param string $value
     *
     * @return GpioInterface
     */
    public function write($pinNo, $value);

    /**
     * Unexport Pin
     *
     * @param int $pinNo
     *
     * @return GpioInterface
     */
    public function unexport($pinNo);

    /**
     * Unexport all pins
     *
     * @return GpioInterface
     */
    public function unexportAll();

    /**
     * Check if pin is exported
     *
     * @param int $pinNo
     *
     * @return boolean
     */
    public function isExported($pinNo);

    /**
     * get the pin's current direction
     *
     * @param int $pinNo
     *
     * @return string string pin's direction value or boolean false
     */
    public function getCurrentDirection($pinNo);

    /**
     * Check for valid direction, in or out
     *
     * @param string $direction
     *
     * @return boolean
     */
    public function isValidDirection($direction);

    /**
     * Check for valid output value
     *
     * @param mixed $output
     *
     * @return boolean
     */
    public function isValidOutput($output);

    /**
     * Check for valid pin value
     *
     * @param int $pinNo
     *
     * @return boolean
     */
    public function isValidPin($pinNo);
}
