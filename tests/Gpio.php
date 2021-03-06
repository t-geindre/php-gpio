<?php

namespace PhpGpio\tests\units;

use PhpGpio\GpioInterface;
use atoum;

class Gpio extends atoum
{
    public function testConstruct()
    {
        $self = $this;
        $this
            ->given(
                $pins = [1,2,3],
                $this->newTestedInstance($pins)
            )
            ->then
                ->array($this->testedInstance->getPins())
                    ->isEqualTo($pins)
                ->exception(function() use ($self) {
                    $self->newTestedInstance([]);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Pins list must, at least, contains one pin')
                ->exception(function() use ($self) {
                    $self->newTestedInstance([1,2,'foo']);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Pins list can only contains integer, string found')
        ;
    }

    public function testSetup()
    {
        $this
            ->given(
                $pins = [1,2,3],
                $testedInstance = $this->newTestedInstance($pins),
                $this->function->file_put_contents = true,
                $testedPin = 1,
                $invalidPin = -1
            )
            ->then
                // Invalid pin
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $testedInstance->setup($invalidPin, GpioInterface::DIRECTION_OUT);
                })
                    ->isInstanceOf('InvalidArgumentException')
                // Wrong direction
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->setup($testedPin, "foo");
                })
                    ->isInstanceOf('InvalidArgumentException')
                // Success
                ->object($this->testedInstance->setup($testedPin, GpioInterface::DIRECTION_OUT))
                    ->isTestedInstance()
                    ->function('file_put_contents')
                        ->wasCalled()->twice()
        ;
    }

    public function testRead()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance([1,2,3]),
                $this->function->file_exists = false,
                $testedPin = 1,
                $invalidPin = -1
            )
            ->then
                // Invalid pin
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $testedInstance->read($invalidPin);
                })
                    ->isInstanceOf('InvalidArgumentException')
                // Not exported
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->read($testedPin);
                })
                    ->isInstanceOf('RuntimeException')
            ->then(
                $contents = [GpioInterface::DIRECTION_IN, 'foo'],
                $this->function->file_exists = true,
                $this->function->file_get_contents = function() use (&$contents) {
                    return array_shift($contents);
                }
            )
                // Success
                ->string($testedInstance->read($testedPin))
                    ->isEqualTo('foo')
            ->then(
                $this->function->file_get_contents = GpioInterface::DIRECTION_OUT
            )
                // Wrong direction
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->read($testedPin);
                })
                    ->isInstanceOf('RuntimeException')
                    ->hasMessage('Wrong direction "out", "in" expected')
            ->then(
                $this->function->file_get_contents = false,
                $this->function->is_readable = true
            )
                // Not readable, unknow reason
               ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->read($testedPin);
                })
                    ->isInstanceOf('PhpGpio\IOException')
                    ->hasMessage('Cannot read "/sys/class/gpio/gpio1/direction" for an unkown reason')
            ->then(
                $this->function->is_readable = false
            )
                // Not readable
               ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->read($testedPin);
                })
                    ->isInstanceOf('PhpGpio\IOException')
                    ->hasMessage('"/sys/class/gpio/gpio1/direction" not readable, make sur required permissions are available')
        ;
    }

    public function testWrite()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance([1,2,3]),
                $invalidPin = -1,
                $testedPin = 1,
                $this->function->file_exists = false
            )
            ->then
                // Invalid pin
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $testedInstance->write($invalidPin, GpioInterface::IO_VALUE_ON);
                })
                    ->isInstanceOf('InvalidArgumentException')
                // Not exported
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->write($testedPin, GpioInterface::IO_VALUE_ON);
                })
                    ->isInstanceOf('RuntimeException')
            ->then(
                $this->function->file_exists = true,
                $this->function->file_get_contents = GpioInterface::DIRECTION_OUT,
                $this->function->file_put_contents = strlen(GpioInterface::IO_VALUE_ON)
            )
                // Success
                ->object($testedInstance->write($testedPin, GpioInterface::IO_VALUE_ON))
                    ->isIdenticalTo($testedInstance)
            ->then(
                $this->function->file_get_contents = GpioInterface::DIRECTION_IN
            )
                // Wrong direction
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->write($testedPin, GpioInterface::IO_VALUE_ON);
                })
                    ->isInstanceOf('RuntimeException')
                    ->hasMessage('Wrong direction "in", "out" expected')
            ->then(
                $this->function->file_get_contents = GpioInterface::DIRECTION_OUT,
                $this->function->file_put_contents = false,
                $this->function->is_writeable = true
            )
                // Not writeable, unknow reason
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->write($testedPin, GpioInterface::IO_VALUE_ON);
                })
                    ->isInstanceOf('PhpGpio\IOException')
                    ->hasMessage('Cannot write "/sys/class/gpio/gpio1/value" for an unkown reason')
            ->then(
                $this->function->is_writeable = false
            )
                // Not writeable
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->write($testedPin, GpioInterface::IO_VALUE_ON);
                })
                    ->isInstanceOf('PhpGpio\IOException')
                    ->hasMessage('"/sys/class/gpio/gpio1/value" not writable, make sur required permissions are available')
        ;
    }

    public function testUnexport()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance($pins = [1,2,3]),
                $this->function->file_exists = false
            )
                // Success, not exported
                ->object($this->testedInstance->unexport($pins[0]))
                    ->isIdenticalTo($this->testedInstance)
                ->function('file_put_contents')
                    ->wasCalled()->never()
            ->then(
                $this->function->file_put_contents = 10,
                $this->function->file_exists = true
            )
                // Success
                ->object($this->testedInstance->unexport($pins[0]))
                    ->isIdenticalTo($this->testedInstance)
                ->function('file_put_contents')
                    ->wasCalled()->once()
            ->then(
                $this->function->file_exists = true,
                $this->function->file_put_contents = false
            )
                // Not writeable
                ->exception(function() use ($testedInstance, $pins) {
                    $testedInstance->unexport($pins[0]);
                })
                    ->isInstanceOf('PhpGpio\IOException')

        ;
    }

    public function testUnexportAll()
    {
        $this
            ->given(
                $this->newTestedInstance($pins = [1,2,3]),
                $exists = [false, false, false, true, true, true],
                $this->function->file_exists = function() use (&$exists) {
                    return array_shift($exists);
                },
                $this->function->file_put_contents = 10,
                $this->testedInstance
                    ->setup($pins[0], GpioInterface::DIRECTION_OUT)
                    ->setup($pins[1], GpioInterface::DIRECTION_OUT)
                    ->setup($pins[2], GpioInterface::DIRECTION_OUT)
            )
                ->object($this->testedInstance->unexportAll())
                    ->isIdenticalTo($this->testedInstance)
                ->function('file_put_contents')
                    ->wasCalled()->exactly(9)
        ;
    }

    public function testIsExported()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance($pins = [1,2,3]),
                $this->function->file_exists = false,
                $invalidPin = -1
            )
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $this->testedInstance->isExported($invalidPin);
                })
                    ->isInstanceOf('InvalidArgumentException')
                ->exception(function() use ($testedInstance, $pins) {
                    $testedInstance->isExported($pins[0], true);
                })
                    ->isInstanceOf('RuntimeException')
                ->boolean($this->testedInstance->isExported($pins[0]))
                    ->isFalse()
            ->then(
                $this->function->file_exists = true
            )
                ->boolean($this->testedInstance->isExported($pins[0]))
                    ->isTrue()
        ;
    }

    public function testGetCurrentDirection()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance($pins = [1,2,3]),
                $this->function->file_exists = false,
                $this->function->file_get_contents = false,
                $invalidPin = -1
            )
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $testedInstance->getCurrentDirection($invalidPin);
                })
                    ->isInstanceOf('InvalidArgumentException')
                ->exception(function() use ($testedInstance, $pins) {
                    $testedInstance->getCurrentDirection($pins[0]);
                })
                    ->isInstanceOf('RuntimeException')
            ->then(
                $this->function->file_exists = true
            )
                ->exception(function() use ($testedInstance, $pins) {
                    $testedInstance->getCurrentDirection($pins[0]);
                })
                    ->isInstanceOf('PhpGpio\IOException')
            ->then(
                $this->function->file_get_contents = GpioInterface::DIRECTION_OUT . ' '
            )
                ->string($testedInstance->getCurrentDirection($pins[0]))
                    ->isEqualTo(GpioInterface::DIRECTION_OUT)
        ;
    }

    public function testIsValidDirection()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance([1,2,3])
            )
                ->exception(function() use ($testedInstance) {
                    $testedInstance->isValidDirection(1, true);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Direction "1" is invalid (string expected)')
                ->boolean($this->testedInstance->isValidDirection(1))
                    ->isFalse()
                ->exception(function() use ($testedInstance) {
                    $testedInstance->isValidDirection('', true);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Direction "" is invalid (string expected)')
                ->boolean($this->testedInstance->isValidDirection(''))
                    ->isFalse()
                ->exception(function() use ($testedInstance) {
                    $testedInstance->isValidDirection('foo', true);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Direction "foo" is invalid (unknown direction)')
                ->boolean($this->testedInstance->isValidDirection('foo'))
                    ->isFalse()
                ->boolean($this->testedInstance->isValidDirection(GpioInterface::DIRECTION_OUT))
                    ->isTrue()
                ->boolean($this->testedInstance->isValidDirection(GpioInterface::DIRECTION_IN))
                    ->isTrue()
        ;
    }

    public function testIsValidPin()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance($pins = [1,2,3])
            )
                ->exception(function() use ($testedInstance) {
                    $testedInstance->isValidPin(0, true);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Pin number "0" is invalid (out of exepected range)')
                ->exception(function() use ($testedInstance) {
                    $testedInstance->isValidPin('string', true);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Pin number "string" is invalid (integer expected)')
                ->boolean($testedInstance->isValidPin(0))
                    ->isFalse()
                ->boolean($testedInstance->isValidPin('string'))
                    ->isFalse()
                ->boolean($testedInstance->isValidPin($pins[0], true))
                    ->isTrue()
                ->boolean($testedInstance->isValidPin($pins[0], false))
                    ->isTrue()
        ;
    }
}
