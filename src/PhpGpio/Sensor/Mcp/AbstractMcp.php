<?php

namespace PhpGpio\Sensor\Mcp;

use PhpGpio\GpioInterface;
use PhpGpio\Sensor\SensorInterface;

/**
 * Common code to cummunication with MCP ADC
 */
abstract class AbstractMcp implements SensorInterface
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
     * @var integer
     */
    protected $channelsCount;

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

    /**
     * {@inheritdoc}
     */
    public function read(array $args = [])
    {
        $channel = isset($args['channel']) ? $args['channel'] : null;
        if (!is_integer($channel) || $channel < 0 || $channel > $this->channelsCount - 1) {
            throw new \InvalidArgumentException(
                sprintf('Invalid channel, %s channels available starting from 0', $this->channelsCount, $this->channelsCount)
            );
        }

        $this->initCom();
        $this->selectChannel($args);

        return $this->readSelectedChannel();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $args = [])
    {
        throw new \RuntimeException('Component not writable');
    }

    protected function initCom()
    {
        $this->gpio->write($this->csPin, GpioInterface::IO_VALUE_ON);
        $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_OFF);
        $this->gpio->write($this->csPin, GpioInterface::IO_VALUE_OFF);
    }

    protected function selectChannel($args)
    {
        $channelCode = $this->getChannelCode($args);

        foreach ($channelCode as $code) {
            $this->gpio->write(
                $this->mosiPin,
                $code ? GpioInterface::IO_VALUE_ON : GpioInterface::IO_VALUE_OFF
            );
            $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_ON);
            $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_OFF);
        }
    }

    protected function readSelectedChannel()
    {
        $adcout = 0;
        //  read in one empty bit, one null bit and 10 ADC bits
        for ($i = 0; $i < 12; $i++) {
            $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_ON);
            $this->gpio->write($this->clockPin, GpioInterface::IO_VALUE_OFF);
            $adcout <<= 1;
            if ($this->gpio->read($this->misoPin)) {
                $adcout |= 0x1;
            }
        }

        $this->gpio->write($this->csPin, GpioInterface::IO_VALUE_ON);

        return $adcout >> 1;
    }

    abstract protected function getChannelCode(array $args);
}
