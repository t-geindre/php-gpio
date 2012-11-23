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
	$this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->setup(array());
        $this->assertTrue($result instanceof DS18B20);
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithWrongNullParameter()
    {
	$this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $this->sensor->setup(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithWrongStringParameter()
    {
	$this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $this->sensor->setup('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithWrongIntParameter()
    {
	$this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $this->sensor->setup(1);
    }

}
