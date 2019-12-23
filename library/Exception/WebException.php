<?php

namespace Library\Exception;

use Exception;
use Throwable;

class WebException extends Exception
{
    /**
     * @var int $status http返回状态
     */
    private $status;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, int $status = null)
    {
        parent::__construct($message, $code, $previous);
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status ?: $this->code;
    }
}