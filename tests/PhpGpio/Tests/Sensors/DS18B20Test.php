<?php

namespace PhpGpio\Tests\Sensors;

use PhpGpio\Sensors\DS18B20;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class DS18B20Test extends \PhpUnit_Framework_TestCase
{
    private $sensor;
    private $rpi = 'rypi';

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
     * a valid read test
     */
    public function testRead()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->read();
        $this->assertTrue(is_float($result));
    }

    public function testSetupWithWrongEmptyArray()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->setup(array());
        $this->assertFalse($result->getBus());
    }

    public function testSetupWithWrongNullParameter()
    {
        $result = $this->sensor->setup(null);
        $this->assertFalse($result->getBus());
    }

    /**
     * Testing sesnor's setup() method with a valid filePath for the bus arg
     */
    public function testSetupWithRightBusParameter()
    {
        $result = $this->sensor->setup(array('bus'=>'/etc/hosts'));
        $this->assertTrue(file_exists($result->getBus()));
    }

    public function testSetupWithWrongStringParameter()
    {
        $result = $this->sensor->setup('foo');
        $this->assertFalse($result->getBus());
    }

    public function testSetupWithWrongIntParameter()
    {
        $result = $this->sensor->setup(1);
        $this->assertFalse($result->getBus());
    }

}
