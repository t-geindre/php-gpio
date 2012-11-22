<?php

namespace PhpGpio;

class Gpio {

    // Using BCM pin numbers.
    private $pins = array(
        0, 1, 4, 7, 8, 9,
        10, 11, 14, 15, 17, 18,
        21, 22, 23, 24, 25
    );

    private $directions = array(
        'in', 'out'
    );

    // exported pins for when we unexport all
    private $exportedPins = array();

    // Setup pin, takes pin number and direction (in or out)
    public function setup($pinNo, $direction) {
        if(!$this->isValidePin($pinNo)) {
            return false;
        }

        // if exported, unexport it first
        if($this->isExported($pinNo)) {
            $this->unexport($pinNo);
        }

        // Export pin
        file_put_contents('/sys/class/gpio/export', $pinNo);

        // if valid direction then set direction
        if($this->isValidDirection($direction)) {
            file_put_contents('/sys/class/gpio/gpio'.$pinNo.'/direction', $direction);
        }

        // Add to exported pins array
        $exportedPins[] = $pinNo;

        return this;
    }

    public function input($pinNo) {
        if(!$this->isValidePin($pinNo)) {
            return false;
        }
        if($this->isExported($pinNo)) {
            if($this->currentDirection($pinNo) != "out") {
               return file_get_contents('/sys/class/gpio/gpio'.$pinNo.'/value');
            }
            throw new \Exception('Error!' . $this->currentDirection($pinNo) . ' is a wrong direction for this pin!');
        }
        return false;
    }

    // Value == 1 or 0, where 1 = on, 0 = off
    public function output($pinNo, $value) {
        if(!$this->isValidePin($pinNo)) {
            return false;
        }
        if(empty($value)) {
            throw new \InvalidArgumentException(sprintf('value "%s" is invalid.', $value));
        }

        if($this->isExported($pinNo)) {
            if($this->currentDirection($pinNo) != "in") {
                file_put_contents('/sys/class/gpio/gpio'.$pinNo.'/value', $value);
            } else {
                throw new \Exception('Error! Wrong Direction for this pin! Meant to be out while it is ' . $this->currentDirection($pinNo));
            }
        }

        return $this;
    }

    public function unexport($pinNo) {
        if(!$this->isValidePin($pinNo)) {
            return false;
        }
       if($this->isExported($pinNo)) {
            file_put_contents('/sys/class/gpio/unexport', $pinNo);
            foreach ($this->exportedPins as $key => $value) {
                if($value == $pinNo) unset($key);
            }
        }

        return $this;
    }

    public function unexportAll() {
        foreach ($this->exportedPins as $key => $pinNo) file_put_contents('/sys/class/gpio/unexport', $pinNo);
        $this->exportedPins = array();
    }

    // Check if exported
    public function isExported($pinNo) {
        if(!$this->isValidePin($pinNo)) {
            return false;
        }

        return file_exists('/sys/class/gpio/gpio'.$pinNo);
    }

    public function currentDirection($pinNo) {
        if(!$this->isValidePin($pinNo))
        {
            return false;
        }

        return file_get_contents('/sys/class/gpio/gpio'.$pinNo.'/direction');
    }

    // Check for valid direction, in or out
    public function isValidDirection($direction) {
        if(!is_string($direction) || empty($direction)) {
            throw new \InvalidArgumentException(sprintf('Direction "%s" is invalid (string expected).', $direction));
        }
        if (!in_array($pinNo, $this->directions)) {
            throw new \InvalidArgumentException(sprintf('Direction "%s" is invalid (unknown direction).', $direction));
        }

        return true;
    }

    // Check for valid pin
    public function isValidPin($pinNo) {
        if(!is_int($pinNo)) {
            throw new \InvalidArgumentException(sprintf('Pin number "%s" is invalid (integer expected).', $pinNo));
        }
        if (!in_array($pinNo, $this->pins)) {
            throw new \InvalidArgumentException(sprintf('Pin number "%s" is invalid (out of exepected range).', $pinNo));
        }

        return true;
    }
}
?>
