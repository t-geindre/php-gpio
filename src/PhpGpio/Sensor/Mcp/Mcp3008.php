<?php

namespace PhpGpio\Sensor\Mcp;

use PhpGpio\GpioInterface;

/**
 * The MCP3008 has a 10-bit analog to digital converter
 */
class Mcp3008 extends AbstractMcp
{
    /**
     * {@inheritdoc}
     */
    protected $channelsCount = 8;

    protected function getChannelCode(array $args)
    {
        $code = [$args['diff'] ? 0 : 1];
        $channel = $args['channel'];

        $channel <<= 5;
        for ($i = 1; $i < 4; $i++) {
            $code[$i] = $channel & 0x80 ? 1 : 0;
            $channel <<= 1;
        }

        return $code;
    }
}
