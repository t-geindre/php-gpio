<?php

namespace PhpGpio\Sensor\Mcp;

use PhpGpio\Sensor\AbstractSpi;
use PhpGpio\GpioInterface;

/**
 * Common code to cummunication with MCP ADC
 */
abstract class AbstractMcp extends AbstractSpi
{
    /**
     * @var integer
     */
    protected $channelsCount;

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

    protected function selectChannel($args)
    {
        $channelCode = $this->getChannelCode($args);

        foreach ($channelCode as $code) {
            $this->gpio->write(
                $this->mosiPin,
                $code ? GpioInterface::IO_VALUE_ON : GpioInterface::IO_VALUE_OFF
            );
            $this->tick();
        }
    }

    protected function readSelectedChannel()
    {
        $adcout = 0;
        //  read in one empty bit, one null bit and 10 ADC bits
        for ($i = 0; $i < 12; $i++) {
            $this->tick();
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
