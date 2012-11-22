<?php

namespace PhpGpio\Tests\PhpGio;

use PhpGpio\Gpio;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class GpioTest extends \PhpUnit_Framework_TestCase
{
    private $gpio;

    public function setUp()
    {
        $this->gpio = new Gpio();
    }

    /**
     * a valid test
     */
    public function testSetupWithRightParamters()
    {
	$result = $this->gpio->setup(17, 'out');
	$this->assertTrue($result instanceof Gpio);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithNegativePinAndGoodDirection()
    {
        $this->gpio->setup(-1, 'out');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithNullPinAndGoodDirection()
    {
        $this->gpio->setup(null, 'out');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithWrongPinAndGoodDirection()
    {
        $this->gpio->setup('wrongPin', 'out');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithGoodPinAndWrongDirection()
    {
        $this->gpio->setup(17, 'wrongDirection');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetupWithGoodPinAndNullDirection()
    {
        $this->gpio->setup(17, null);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSetupWithMissingArguments()
    {
        $this->gpio->setup(17);
    }


}
