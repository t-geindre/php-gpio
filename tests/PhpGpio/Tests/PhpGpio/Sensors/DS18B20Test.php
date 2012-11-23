<?php

namespace PhpGpio\Tests\PhpGio;

use PhpGpio\Sensors\DS18B20;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class SensorTest extends \PhpUnit_Framework_TestCase
{
    private $sensor;
    private $rpi ='raspberrypi';

    public function setUp()
    {
        $this->sensor = new DS18B20();
    }

    /**
     * @outputBuffering enabled
     */
    public function assertPreconditionOrMarkTestSkipped()
    {
        if ($this->rpi !== $nodename = exec('uname --nodename')) {
            $warning = sprintf(" Precondition is not met : %s is not a %s machine! ", $nodename, $this->rpi);
            $this->markTestSkipped($warning);
        }
    }

    /**
     * a valid setup test
     */
    public function testSetupWithEmptyArray()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->setup(array());
        $this->assertFalse($result);
    }

    /**
     * a valid read test
     */
    public function testRead()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->read();
        $this->assertTrue(is_float($result));
    }

    public function testSetupWithWrongNullParameter()
    {
        $result = $this->sensor->setup(null);
        $this->assertFalse($result);
    }

    public function testSetupWithWrongStringParameter()
    {
        $result = $this->sensor->setup('foo');
        $this->assertFalse($result);
    }

    public function testSetupWithWrongIntParameter()
    {
        $result = $this->sensor->setup(1);
        $this->assertFalse($result);
    }

}
