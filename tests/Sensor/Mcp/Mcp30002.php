<?php

namespace PhpGpio\Sensor\Mcp\tests\units;

use PhpGpio\GpioInterface;
use atoum;

class Mcp3002 extends atoum
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
                    $testedInstance->read();
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Invalid channel, 2 channels available starting from 0')
                ->exception(function() use ($testedInstance) {
                    $testedInstance->read(['channel' => 'foo']);
                })
                    ->isInstanceOf('InvalidArgumentException')
                ->exception(function() use ($testedInstance) {
                    $testedInstance->read(['channel' => 8]);
                })
                    ->isInstanceOf('InvalidArgumentException')
            ->then(
                // 7 on 10 bits, first and last bits are ignored
                $readStack = [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0],
                $gpio = $this->getGpioMock(array_values($this->mapping), $readStack),
                $testedInstance = $this->newInstance($gpio)
            )
                ->integer($testedInstance->read(['channel' => 0]))
                ->isEqualTo(7)
                ->mock($gpio)
                    ->call('write')
                        ->withArguments($this->mapping['cs'], GpioInterface::IO_VALUE_ON)->twice()
                        ->withArguments($this->mapping['cs'], GpioInterface::IO_VALUE_OFF)->once()
                        ->withArguments($this->mapping['clock'], GpioInterface::IO_VALUE_ON)->exactly(14)
                        ->withArguments($this->mapping['clock'], GpioInterface::IO_VALUE_OFF)->exactly(15)
                        ->withArguments($this->mapping['miso'], GpioInterface::IO_VALUE_ON)->never()
                        ->withArguments($this->mapping['miso'], GpioInterface::IO_VALUE_OFF)->never()
                        ->withArguments($this->mapping['mosi'], GpioInterface::IO_VALUE_ON)->once()
                        ->withArguments($this->mapping['mosi'], GpioInterface::IO_VALUE_OFF)->once()
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
