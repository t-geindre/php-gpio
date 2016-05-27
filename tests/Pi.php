<?php

namespace PhpGpio\tests\units;

use atoum;

class Pi extends atoum
{
    public function testGetVersion()
    {
        $this
            ->given(
                $data = [
                    'Revision: 1',
                    'Revision:F', // 15
                    'Revision :FF' // 255
                ],
                $this->function->file_get_contents = function($file) use ($data) {
                    static $index = 0;
                    if ($file == '/proc/cpuinfo') {
                        return $data[$index++];
                    }
                    return '';
                },
                $this->newTestedInstance
            )
            ->then
                ->integer($this->testedInstance->getVersion())
                    ->isEqualTo(1)
                    ->function('file_get_contents')->wasCalled()->once()
                ->integer($this->testedInstance->getVersion())
                    ->isEqualTo(15)
                    ->function('file_get_contents')->wasCalled()->twice()
                ->integer($this->testedInstance->getVersion())
                    ->isEqualTo(255)
                    ->function('file_get_contents')->wasCalled()->exactly(3)
        ;
    }

    public function testGetCpuLoad()
    {
        $this
            ->given(
                $this->function->sys_getloadavg = $cpuLoad = ['foo' => 'bar'],
                $this->newTestedInstance
            )
            ->then
                ->array($this->testedInstance->getCpuLoad())
                    ->isEqualTo($cpuLoad)
                    ->function('sys_getloadavg')->wasCalled()->once()
        ;
    }

    public function testGetCpuTemp()
    {
        $this
            ->given(
                $this->function->file_get_contents = function($file) {
                    if ($file == '/sys/class/thermal/thermal_zone0/temp') {
                        return (string)(10 * 1000);
                    }
                    return '';
                },
                $this->newTestedInstance
            )
            ->then
                ->float($this->testedInstance->getCpuTemp())
                    ->isEqualTo(10)
                ->float($this->testedInstance->getCpuTemp(true))
                    ->isEqualTo(50)
        ;
    }
}
