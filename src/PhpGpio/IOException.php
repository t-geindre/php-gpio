<?php
namespace PhpGpio;

/**
 * Exception class thrown when a filesystem operation failure happens.
 */
class IOException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @param string          $message
     * @param integer         $code
     * @param \Exception|null $previous
     * @param string          $path
     */
    public function __construct($message, $code = 0, \Exception $previous = null, $path = null)
    {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }
    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }
}
