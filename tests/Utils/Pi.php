<?php

namespace PhpGpio\Utils\tests\units;

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

    public function testGetAvailablePins()
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
                ->array($this->testedInstance->getAvailablePins())
                    ->isEqualTo([0, 1, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 21, 22, 23, 24, 25])
                ->array($this->testedInstance->getAvailablePins())
                    ->isEqualTo([2, 3, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 22, 23, 24, 25, 27])
                ->array($this->testedInstance->getAvailablePins())
                    ->isEqualTo([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27])
        ;
    }
}
