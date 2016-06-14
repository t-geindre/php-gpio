<?php

namespace PhpGpio\Sensor\Mcp;

use PhpGpio\GpioInterface;

/**
 * The MCP3002 has a 10-bit analog to digital converter
 */
class Mcp3002 extends AbstractMcp
{
    /**
     * {@inheritdoc}
     */
    protected $channelsCount = 2;

    protected function getChannelCode(array $args)
    {
        return [$args['diff'] ? 0 : 1, $args['channel']];
    }
}
