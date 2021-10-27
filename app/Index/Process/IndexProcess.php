<?php

namespace App\Index\Process;

use Library\Abstracts\Process\AbstractProcess;

class IndexProcess extends AbstractProcess
{
    public function __construct()
    {
        parent::__construct(5);
    }

    public function main()
    {
        echo 'fuck' . PHP_EOL;
        sleep(3);
    }
}