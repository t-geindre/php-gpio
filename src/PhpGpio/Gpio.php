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
     * @param array $pins
     */
    public function __construct(array $pins)
    {
        foreach ($pins as $pin) {
            if (!is_int($pin)) {
                throw new \InvalidArgumentException(
                    sprintf('Pins list can only contains integer, %s found', gettype($pin))
                );
            }
        }

        $this->pins = $pins;
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
        $this->isValidDirection($direction, true);

        if ($this->isExported($pinNo)) {
            $this->unexport($pinNo);
        }

        // Export pin and set direction
        $this->filePutContents(GpioInterface::PATH_EXPORT, $pinNo);
        $this->filePutContents(GpioInterface::PATH_GPIO.$pinNo.'/direction', $direction);

        $this->exportedPins[$pinNo] = true;

        return $this;
    }

    /**
     * Read input value
     *
     * @param int $pinNo
     *
     * @return string GPIO value or boolean false
     */
    public function read($pinNo)
    {
        $this->isExported($pinNo, true);

        if (($dir = $this->currentDirection($pinNo)) != GpioInterface::DIRECTION_IN) {
            throw new \RuntimeException(
                sprintf('Wrong direction "%s", "%s" expected', $dir, GpioInterface::DIRECTION_IN)
            );
        }

        return trim($this->fileGetContents(GpioInterface::PATH_GPIO.$pinNo.'/value'));
    }

    /**
     * Write output value
     *
     * @param int    $pinNo
     * @param string $value
     *
     * @return mixed Gpio current instance or boolean false
     */
    public function write($pinNo, $value)
    {
        $this->isExported($pinNo, true);
        $this->isValidOutput($value, true);

        if (($dir = $this->currentDirection($pinNo)) != GpioInterface::DIRECTION_OUT) {
            throw new \RuntimeException(
                sprintf('Wrong direction "%s", "%s" expected', $this->currentDirection($pinNo), GpioInterface::DIRECTION_IN)
            );
        }

        $this->filePutContents(GpioInterface::PATH_GPIO.$pinNo.'/value', $value);

        return $this;

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

        $this->filePutContents(GpioInterface::PATH_UNEXPORT, $pinNo);

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
            if ($exported) {
                $this->unexport($pinNo);
            }
        }

        return $this;
    }

    /**
     * Check if pin is exported
     *
     * @param integer $pinNo
     * @param boolean $exception
     *
     * @return boolean
     */
    public function isExported($pinNo, $exception = false)
    {
        $this->isValidPin($pinNo);

        if (!file_exists(GpioInterface::PATH_GPIO.$pinNo)) {
            if ($exception) {
                throw new \RuntimeException(sprintf('Pin "%s" not exported', $pinNo));
            }

            return false;
        }

        if (!isset($this->pins[$pinNo]) || !$this->pins[$pinNo]) {
            if (!exception) {
                throw new \RuntimeException(
                    sprintf('Pin "%s" exported but not managed by this instance', $pinNo)
                );
            }

            return false;
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

        return trim($this->fileGetContents(GpioInterface::PATH_GPIO.$pinNo.'/direction'));
    }

    /**
     * Check for valid direction, in or out
     *
     * @param string  $direction
     * @param boolean $exception Throw exception on invalid direction
     *
     * @return boolean true
     */
    public function isValidDirection($direction, $exception = false)
    {
        if (!is_string($direction) || empty($direction)) {
            if ($exception) {
                throw new \InvalidArgumentException(sprintf('Direction "%s" is invalid (string expected).', $direction));
            }

            return false;
        }

        if (!in_array($direction, $this->directions)) {
            if ($exception) {
                throw new \InvalidArgumentException(sprintf('Direction "%s" is invalid (unknown direction).', $direction));
            }

            return false;
        }

        return true;
    }

    /**
     * Check for valid output value
     *
     * @param integer $output
     * @param boolean $exception Throw exception on invalid output
     *
     * @return boolean true
     */
    public function isValidOutput($output, $exception = false)
    {
        if (!is_int($output)) {
            if ($exception) {
                throw new \InvalidArgumentException(sprintf('Pin value "%s" is invalid (integer expected).', $output));
            }

            return false;
        }

        if (!in_array($output, $this->outputs)) {
            if ($exception) {
                throw new \InvalidArgumentException(sprintf('Output value "%s" is invalid (out of exepected range).', $output));
            }

            return false;
        }

        return true;
    }

    /**
     * Check for valid pin value
     *
     * @param integer $pinNo
     * @param boolean $exception Throw exception on invalid pin
     *
     * @return boolean true
     */
    public function isValidPin($pinNo, $exception = false)
    {
        if (!is_int($pinNo)) {
            if ($exception) {
                throw new \InvalidArgumentException(sprintf('Pin number "%s" is invalid (integer expected).', $pinNo));
            }

            return false;
        }

        if (!in_array($pinNo, $this->pins)) {
            if ($exception) {
                throw new \InvalidArgumentException(sprintf('Pin number "%s" is invalid (out of exepected range).', $pinNo));
            }

            return false;
        }

        return true;
    }

    protected function filePutContents($file, $data)
    {
        if (($ret = @file_put_contents($file, $data)) === false) {
            if (!is_writeable($file)) {
                throw new IOException(sprintf('"%s" not writable, make sur required permissions are available', $file), 0, null, $file);
            }
            throw new IOException(sprintf('Cannot write "%s" for an unkown reason', $file), 0, null, $file);
        }

        return $ret;
    }

    protected function fileGetContents($file)
    {
        if (($ret = @file_get_contents($file, $data)) === false) {
            if (!is_readable($file)) {
                throw new IOException(sprintf('"%s" not readable, make sur required permissions are available', $file), 0, null, $file);
            }
            throw new IOException(sprintf('Cannot read "%s" for an unkown reason', $file), 0, null, $file);
        }

        return $ret;
    }
}
