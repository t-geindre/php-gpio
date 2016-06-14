<?php
namespace PhpGpio\Sensor;

use PhpGpio\GpioInterface;

/**
 * Common code for SPI sensors
 */
abstract class AbstractSpi implements SensorInterface
{
	/**
     * @var integer
     */
    protected $clockPin;

    /**
     * @var integer
     */
    protected $mosiPin;

    /**
     * @var integer
     */
    protected $misoPin;

    /**
     * @var integer
     */
    protected $csPin;

    /**
     * @var Gpio
     */
    protected $gpio;

    /**
     * @param GpioInterface $gpio     Gpio instance
     * @param integer       $clockpin The clock (CLK) pin (ex. 11)
     * @param integer       $mosipin  The Master Out Slave In (MOSI) pin (ex. 10)
     * @param integer       $misopin  The Master In Slave Out (MISO) pin (ex. 9)
     * @param integer       $cspin    The Chip Select (CSna) pin (ex. 8)
     */
    public function __construct(GpioInterface $gpio, $clockpin, $mosipin, $misopin, $cspin)
    {
        $this->gpio = $gpio;

        $this->clockPin = $clockpin;
        $this->mosiPin = $mosipin;
        $this->misoPin = $misopin;
        $this->csPin = $cspin;

        $this->gpio->setup($this->mosiPin, GpioInterface::DIRECTION_OUT);
        $this->gpio->setup($this->misoPin, GpioInterface::DIRECTION_IN);
        $this->gpio->setup($this->clockPin, GpioInterface::DIRECTION_OUT);
        $this->gpio->setup($this->csPin, GpioInterface::DIRECTION_OUT);
    }

    protected function tick()
    {
        $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_ON);
        $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_OFF);
    }

    protected function initCom()
    {
        $this->gpio->write($this->csPin, GpioInterface::IO_VALUE_ON);
        $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_OFF);
        $this->gpio->write($this->csPin, GpioInterface::IO_VALUE_OFF);
    }
}
