<?php

namespace PhpGpio\Sensor\Mcp\tests\units;

use PhpGpio\GpioInterface;
use atoum;

class Mcp3004 extends atoum
{
    protected $mapping = [
        'clock' => 12,
        'mosi' => 16,
        'miso' => 20,
        'cs' => 21
    ];

    public function testRead()
    {
        $this
            ->given(
                $gpio = $this->getGpioMock(array_values($this->mapping)),
                $testedInstance = $this->newInstance($gpio)
            )
            ->then
                ->exception(function() use ($testedInstance) {
                    $testedInstance->read(['channel' => 4]);
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Invalid channel, 4 channels available starting from 0')
        ;
    }

    protected function getGpioMock(array $pins, array $readStack = [])
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $mock = new \mock\PhpGpio\Gpio;

        $mock->getMockCOntroller()->setup = $mock;
        $mock->getMockCOntroller()->write = $mock;
        $mock->getMockCOntroller()->read = function() use (&$readStack) {
            return array_shift($readStack);
        };

        return $mock;
    }

    protected function newInstance(GpioInterface $gpio)
    {
        return $this->newTestedInstance(
            $gpio,
            $this->mapping['clock'],
            $this->mapping['mosi'],
            $this->mapping['miso'],
            $this->mapping['cs']
        );
    }
}
