<?php

namespace PhpGpio\Server\Provider\tests\units;

use atoum;

class GpioProvider extends atoum
{
    public function testValidateArguments()
    {
        $this
            ->given($testedInstance = $this->newTestedInstance())
            ->then

                ->array($this->testedInstance->validateArguments(
                    [['float', true], ['int', true], ['str', true], ['bool', true], ['bool', true], ['bool', true], ['bool', true]],
                    ['10.5', '10', 'foobar', '1', '0', 'TRUE', 'FALSE']
                ))
                    ->isIdenticalTo([10.5, 10, 'foobar', true, false, true, false])

                ->array($this->testedInstance->validateArguments(
                    [['int', true], ['bool', false], ['bool', false]],
                    ['10']
                ))
                    ->isIdenticalTo([10])

                ->array($this->testedInstance->validateArguments(
                    [['int', true], ['str', true], ['bool', false]],
                    ['10', 'foo']
                ))
                    ->isIdenticalTo([10, 'foo'])

                ->exception(function() use ($testedInstance) {
                    $testedInstance->validateArguments([['bool', true]], ['foo']);
                })
                    ->isInstanceOf('\InvalidArgumentException')
                    ->hasMessage('Invalid argument type, position: 1, expected type: bool')
                ->exception(function() use ($testedInstance) {
                    $testedInstance->validateArguments([['int', true]], ['foo']);
                })
                    ->isInstanceOf('\InvalidArgumentException')
                    ->hasMessage('Invalid argument type, position: 1, expected type: int')
                ->exception(function() use ($testedInstance) {
                    $testedInstance->validateArguments([['float', true]], ['foo']);
                })
                    ->isInstanceOf('\InvalidArgumentException')
                    ->hasMessage('Invalid argument type, position: 1, expected type: float')
        ;
    }

    public function testAccept()
    {
        $this
            ->given($testedInstance = $this->newTestedInstance())
            ->then
                ->boolean($this->testedInstance->accept('gpio'))
                    ->isTrue()
                ->boolean($this->testedInstance->accept('foo'))
                    ->isFalse()
        ;
    }

    public function testExecute()
    {

    }
}

