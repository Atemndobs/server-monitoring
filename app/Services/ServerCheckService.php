<?php

namespace App\Services;

class ServerCheckService
{
    public function checkServerProcesses()
    {
        $processes = shell_exec('ps -A');
        //$processes = explode("\n", $processes);
        return $processes;
    }
}
