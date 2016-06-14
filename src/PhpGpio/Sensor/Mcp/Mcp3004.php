<?php

namespace PhpGpio\Sensor\Mcp;

use PhpGpio\GpioInterface;

/**
 * The MCP3004 has a 10-bit analog to digital converter
 */
class Mcp3004 extends Mcp3008
{
    /**
     * {@inheritdoc}
     */
    protected $channelsCount = 4;
}
