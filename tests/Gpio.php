<?php

namespace PhpGpio\tests\units;

use PhpGpio\GpioInterface;
use atoum;

class Gpio extends atoum
{
    public function testConstruct($version, $pins, $hackablePins)
    {
        $this
            ->given(
                $this->newTestedInstance(
                    $this->getPiMock($version)
                )
            )
            ->then
                ->array($this->testedInstance->getHackablePins())
                    ->isEqualTo($hackablePins)
        ;

        foreach ($pins as $pin) {
            $this->boolean($this->testedInstance->isValidPin($pin))
                ->isTrue()
            ;
        }
    }

    protected function testConstructDataProvider()
    {
        return [
            [
                3,
                [0, 1, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 21, 22, 23, 24, 25],
                [4, 7, 8, 9, 10, 11, 17, 18, 21, 22, 23, 24, 25],
            ],
            [
                15,
                [2, 3, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 22, 23, 24, 25, 27],
                [4, 7, 8, 9, 10, 11, 17, 18, 22, 23, 24, 25, 27],
            ],
            [
                1000,
                [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27],
                [4, 5, 6, 12, 13, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27],
            ]
        ];
    }

    public function testSetup()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance($this->getPiMock()),
                $this->function->file_put_contents = true,
                $testedPin = 1,
                $invalidPin = -1
            )
            ->then
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $testedInstance->setup($invalidPin, GpioInterface::DIRECTION_OUT);
                })
                    ->isInstanceOf('InvalidArgumentException')
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->setup($testedPin, "foo");
                })
                    ->isInstanceOf('InvalidArgumentException')
                ->object($this->testedInstance->setup($testedPin, GpioInterface::DIRECTION_OUT))
                    ->isTestedInstance()
                    ->function('file_put_contents')
                        ->wasCalled()->twice()
        ;
    }

    public function testInput()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance($this->getPiMock()),
                $this->function->file_exists = false,
                $testedPin = 1,
                $invalidPin = -1
            )
            ->then
                ->exception(function() use ($testedInstance, $invalidPin) {
                    $testedInstance->input($invalidPin);
                })
                    ->isInstanceOf('InvalidArgumentException')
                ->exception(function() use ($testedInstance, $testedPin) {
                    $testedInstance->input($testedPin);
                })
                    ->isInstanceOf('RuntimeException') // Not exported
            ->then(
                $this->function->file_exists = true,
                $this->function->file_get_contents = 'foo'
            )
                ->string($testedInstance->input($testedPin))
                    ->isEqualTo('foo')
        ;
    }

    public function testOutput()
    {
        $this
            ->given(
                $testedInstance = $this->newTestedInstance(
                    $this->getPiMock()
                ),
                $invalidPin = -1,
                $data = 'foo'
            )
            ->then
                ->exception(function() use ($testedInstance, $invalidPin, $data) {
                    $testedInstance->output($invalidPin, $data);
                })
                    ->isInstanceOf('InvalidArgumentException')
        ;
    }

    protected function getPiMock($version = 2)
    {
        $mock = new \mock\PhpGpio\Pi;
        $mock->getMockController()->getVersion = $version;

        return $mock;
    }
}
