<?php

namespace PhpGpio\Server\Provider;

use React\Socket\ConnectionInterface;

/**
 * Base functions for server providers
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * Arguments type validation mapping
     *
     * @var array
     */
    private $typesMapping = [
        'int'   => ['intval', 'ctype_digit'],
        'str'   => ['strval', null],
        'float' => ['floatval', 'is_numeric'],
        'bool'  => [
            ['0' => false, '1' => true, 'TRUE' => true,  'FALSE' => false],
            null,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    abstract public function accept($token);

    /**
     * {@inheritdoc}
     */
    abstract public function execute(ConnectionInterface $conn, array $arguments);

    /**
     * Validates query arguments
     * All defined and valid arguments are casted to the right type
     * Only lasts arguments can be not required
     *
     * Schema :
     *   [[type, required]]
     *   [['float', true], ['int', true], ['str', true], ['bool', true]]
     *
     * @param array $schema
     * @param array $arguments
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function validateArguments(array $schema, array $arguments)
    {
        $index = -1;
        foreach ($schema as list($type, $required)) {
            $index++;

            if (!array_key_exists($type, $this->typesMapping)) {
                throw new \RuntimeException(sprintf('Unknow type "%s"', $type));
            }

            if (array_key_exists($index, $arguments)) {
                list($transformer, $validator) = $this->typesMapping[$type];

                if (is_null($validator) && !is_array($transformer)) {
                    continue;
                }

                if (is_array($transformer)) {
                    if (array_key_exists($arguments[$index], $transformer)) {
                        $arguments[$index] = $transformer[$arguments[$index]];
                        continue;
                    }
                }

                if (!is_null($validator) && call_user_func($validator, $arguments[$index])) {
                    $arguments[$index] = call_user_func($transformer, $arguments[$index]);
                    continue;
                }

                throw new \InvalidArgumentException(
                    sprintf('Invalid argument type, position: %d, expected type: %s', $index + 1, $type)
                );
            }

            if ($required) {
                throw new \InvalidArgumentException(
                    sprintf('Required argument missing, position: %d, type: %s', $index + 1, $type)
                );
            }
        }

        return $arguments;
    }
}
