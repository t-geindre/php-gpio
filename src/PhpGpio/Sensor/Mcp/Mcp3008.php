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

    /**
     * {@inheritdoc}
     */
    public function read(array $args = [])
    {
        if (isset($channel['diff']) && !is_bool($channel['diff'])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid "diff" value, got "%s", boolean expected',
                gettype($args['diff'])
            ));
        } else {
            $args['diff'] = false;
        }

        return parent::read($args);
    }

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
