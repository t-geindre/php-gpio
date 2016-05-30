<?php

namespace PhpGpio;

/**
 * Gpio R/W access
 */
class Gpio implements GpioInterface
{
    /**
     * @var array
     */
    protected $pins;

    /**
     * @var array
     */
    protected $hackablePins;

    /**
     * @var array
     */
    protected $directions = [
        GpioInterface::DIRECTION_IN,
        GpioInterface::DIRECTION_OUT,
    ];

    /**
     * @var arrray
     */
    protected $outputs = [
        GpioInterface::IO_VALUE_ON,
        GpioInterface::IO_VALUE_OFF,
    ];

    /**
     * @var array
     */
    protected $exportedPins = [];

    /**
     * @param Pi|null $raspi
     */
    public function __construct(Pi $raspi)
    {
        if ($raspi->getVersion() < 4) {
            $this->pins = [0, 1, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 21, 22, 23, 24, 25];
            $this->hackablePins = [4, 7, 8, 9, 10, 11, 17, 18, 21, 22, 23, 24, 25];
        } elseif ($raspi->getVersion() < 16) {
            // new GPIO layout (REV2)
            $this->pins = [2, 3, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 22, 23, 24, 25, 27];
            $this->hackablePins = [4, 7, 8, 9, 10, 11, 17, 18, 22, 23, 24, 25, 27];
        } else {
            // new GPIO layout (B+)
            $this->pins = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
            $this->hackablePins = [4, 5, 6, 12, 13, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
        }
    }

    /**
     * @link http://elinux.org/RPi_Low-level_peripherals
     *
     * @return integer[]
     */
    public function getHackablePins()
    {
        return $this->hackablePins;
    }

    /**
     * Setup pin, takes pin number and direction (in or out)
     *
     * @param int    $pinNo
     * @param string $direction
     *
     * @return mixed  string GPIO value or boolean false
     */
    public function setup($pinNo, $direction)
    {
        $this->isValidDirection($direction);

        if ($this->isExported($pinNo, false)) {
            $this->unexport($pinNo);
        }

        // Export pin
        file_put_contents(GpioInterface::PATH_EXPORT, $pinNo);

        // Set pin direction
        file_put_contents(GpioInterface::PATH_GPIO.$pinNo.'/direction', $direction);

        // Add to exported pins array
        $this->exportedPins[$pinNo] = true;

        return $this;
    }

    /**
     * Get input value
     *
     * @param int $pinNo
     *
     * @return string string GPIO value or boolean false
     */
    public function input($pinNo)
    {
        $this->isExported($pinNo);

        if ($this->currentDirection($pinNo) != GpioInterface::DIRECTION_OUT) {
            return trim(file_get_contents(GpioInterface::PATH_GPIO.$pinNo.'/value'));
        }

        throw new \RuntimeException(
            sprintf('Wrong direction "%s", "%s" expected', $this->currentDirection($pinNo), GpioInterface::DIRECTION_OUT)
        );
    }

    /**
     * Set output value
     *
     * @param int    $pinNo
     * @param string $value
     *
     * @return mixed Gpio current instance or boolean false
     */
    public function output($pinNo, $value)
    {
        $this->isExported($pinNo);
        $this->isValidOutput($value);

        if ($this->currentDirection($pinNo) != GpioInterface::DIRECTION_IN) {
            file_put_contents(GpioInterface::PATH_GPIO.$pinNo.'/value', $value);

            return $this;
        }

        throw new \RuntimeException(
            sprintf('Wrong direction "%s", "%s" expected', $this->currentDirection($pinNo), GpioInterface::DIRECTION_IN)
        );
    }

    /**
     * Unexport Pin
     *
     * @param int $pinNo
     *
     * @return mixed Gpio current instance or boolean false
     */
    public function unexport($pinNo)
    {
        $this->isExported($pinNo);

        file_put_contents(GpioInterface::PATH_UNEXPORT, $pinNo);

        $this->exportedPins[$pinNo] = false;

        return $this;
    }

    /**
     * Unexport all pins
     *
     * @return Gpio Gpio current instance or boolean false
     */
    public function unexportAll()
    {
        foreach ($this->exportedPins as $pinNo => $exported) {
            if ($expoted) {
                file_put_contents(GpioInterface::PATH_UNEXPORT, $pinNo);
            }
            $this->exportedPins[$pinNo] = false;
        }

        return $this;
    }

    /**
     * Check if pin is exported
     *
     * @param integer $pinNo
     * @param boolean $throwExceptions
     *
     * @return boolean
     */
    public function isExported($pinNo, $throwExceptions = true)
    {
        $this->isValidPin($pinNo);

        if (!file_exists(GpioInterface::PATH_GPIO.$pinNo)) {
            if (!$throwExceptions) {
                return false;
            }
            throw new \RuntimeException(sprintf('Pin "%s" not exported', $pinNo));
        }

        if (!isset($this->pins[$pinNo]) || !$this->pins[$pinNo]) {
            if (!$throwExceptions) {
                return false;
            }
            throw new \RuntimeException(
                sprintf('Pin "%s" exported but not managed by this instance', $pinNo)
            );
        }

        return true;
    }

    /**
     * get the pin's current direction
     *
     * @param integer $pinNo
     *
     * @return false|string string pin's direction value or boolean false
     */
    public function currentDirection($pinNo)
    {
        $this->isExported($pinNo);

        return trim(file_get_contents(GpioInterface::PATH_GPIO.$pinNo.'/direction'));
    }

    /**
     * Check for valid direction, in or out
     *
     *  @param string $direction
     *
     * @return boolean true
     */
    public function isValidDirection($direction)
    {
        if (!is_string($direction) || empty($direction)) {
            throw new \InvalidArgumentException(sprintf('Direction "%s" is invalid (string expected).', $direction));
        }

        if (!in_array($direction, $this->directions)) {
            throw new \InvalidArgumentException(sprintf('Direction "%s" is invalid (unknown direction).', $direction));
        }

        return true;
    }

    /**
     * Check for valid output value
     *
     * @param integer $output
     *
     * @return boolean true
     */
    public function isValidOutput($output)
    {
        if (!is_int($output)) {
            throw new \InvalidArgumentException(sprintf('Pin value "%s" is invalid (integer expected).', $output));
        }

        if (!in_array($output, $this->outputs)) {
            throw new \InvalidArgumentException(sprintf('Output value "%s" is invalid (out of exepected range).', $output));
        }

        return true;
    }

    /**
     * Check for valid pin value
     *
     * @param integer $pinNo
     *
     * @return boolean true
     */
    public function isValidPin($pinNo)
    {
        if (!is_int($pinNo)) {
            throw new \InvalidArgumentException(sprintf('Pin number "%s" is invalid (integer expected).', $pinNo));
        }

        if (!in_array($pinNo, $this->pins)) {
            throw new \InvalidArgumentException(sprintf('Pin number "%s" is invalid (out of exepected range).', $pinNo));
        }

        return true;
    }
}
