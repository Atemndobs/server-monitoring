<?php

namespace App\Services;

use App\Models\Process;

/**
 *
 */
class ServerCheckService
{

    /**
     *
     * @param int|string $name
     * @return bool|int
     */
    public function checkRunningProcess(int|string $name): bool|int
    {
        $processes = Process::query()->where('name', $name)->get();
        $port = $this->getPort($name);
        $url = $this->getUrl($name);

       // $netstat = shell_exec("netstat -tulpn | grep $port | grep -v grep"); // netstat -tulpn
        $netstat = shell_exec("sudo netstat -plten |grep $port | grep -v grep"); // netstat -tulpn
        $pidAndCommand = shell_exec("netstat -tulpn | grep $port | grep -v grep | awk '{print $7}'"); // netstat -tulpn | awk '{print $7}'

        try {
            $pid = explode("/", $pidAndCommand)[0];
            $command = explode("/", $pidAndCommand)[1];
            $command = str_replace("\n", "", $command);
        }catch (\Exception $e) {
            $netstat = explode("\n", $netstat);
            $netstat = array_filter($netstat);
            // remove empty array element containing "tcp6"
            $netstat = array_filter($netstat, function ($value) {
                return strpos($value, 'tcp6') === false;
            });
            $netstat = $netstat[0];
            $netstat = explode(" ", $netstat);
            $netstat = array_filter($netstat);
            // reset array keys
            $netstat = array_values($netstat);
            $pid = $netstat[4];
            if ((int)$pid <= 0) {
                $pid = 9999999;
            }
            $command = $netstat[3];
        }

        dump([
            'netstat' => $netstat,
        ]);

       // dump($this->getRunningProcesses($name));
        $candidates = [];
        if ($netstat != null){
            $candidates = [
                'pid' => $pid,
                'name' => $name,
                'command' => $command,
                'port' => $port,
                'url' => $url,
            ];
        }

        if (count($candidates) === 0) {
            dump("No process found for $name : Update Process Status");
            $downProcess = $processes->each(function ($process) {
                /** @var Process $process */
                $process->status = 'down';
                $process->last_checked_at = now();
                $process->save();
                return $process;
            });
            dump($downProcess->toArray());

            if (count($downProcess) > 0) {
                $this->restartProcess($downProcess->first());
            }
            return 0;
        }
        $checked = [];
        if (count($processes) >= 1) {
            foreach ($processes as $process) {
                dump([
                    'process' => $process->pid,
                    'pif' => $pid,
                    'candidate' => $process->pid == $pid,
                ]);
                if ($process->pid == $pid ) {
                    $process->last_checked_at = now();
                    $process->url = $url;
                    $process->save();
                    $checked[] = $process->toarray();
                }else{
                    // check if pid exist in db
                    $existProccess = Process::query()->where('pid', $pid)->get();
                    // if not exist, create new process
                    if (count($existProccess) === 0) {
                        $checked[] = $this->createNewProcess($pid, $command, $name, $url, $port);
                    }
                }
            }
        }else{
            $checked[] = $this->createNewProcess($pid, $command, $name, $url, $port);
        }

        return count($checked) > 0;
    }

    /**
     * @param string|int $name
     * @return string|int
     */
    public function getPort(string|int $name): string|int
    {
        try {
            $servers = [
                'nest' =>  "3000",
                'vue' =>  "8080",
                'artisan' =>  "8899",
            ];

            return $servers[$name];
        }catch (\Exception $e) {
            try {
                $this->getProcessName($name);
            }catch (\Exception $e) {
                $this->getExceptionMessage($e);
                return $name;
            }
            return $name;
        }
    }

    /**
     * @param string|int $name
     * @return string|int
     */
    private function getUrl(string|int $name): string|int
    {
        try {
            $servers = [
                'nest' =>  "http://172.17.0.1:3000",
                'vue' =>  "http://172.17.0.1:8080",
                'artisan' =>  "http://172.17.0.1:8899",
            ];

            return $servers[$name]; // http://localhost:3000
        }catch (\Exception $e) {
            $this->getExceptionMessage($e);
            exit(1);
        }
    }

    /**
     * @param string|int $port
     * @return string
     */
    public function getProcessName(string|int $port) : string
    {
        $portCheck = (int)$port;
        if ($portCheck == 0) {
            return $this->getPort($port);
        }
        else{
            try {
                $service =[
                    '3000' => 'nest',
                    '8080' => 'vue',
                    '8899' => 'artisan',
                ];
                return $service[$port];
            }catch (\Exception $e) {
                try {
                    $this->getPort($port);
                }catch (\Exception $e) {
                    $this->getExceptionMessage($e);
                   return $port;
                }
            }
        }
        return $port;
    }

    /**
     * @param string|int $pid
     * @param string $command
     * @param string $name
     * @param string $url
     * @param string|int $port
     * @return array
     */
    public function createNewProcess(string|int $pid, string $command, string $name, string $url, string|int $port): array
    {
        try {
            $process = new Process();
            $process->pid = $pid;
            $process->command = $command;
            $process->name = $name;
            $process->status = 'running';
            $process->url = $url;
            $process->port = $port;
            $process->last_checked_at = date('Y-m-d H:i:s');
            $process->saveQuietly();
            return $process->toArray();
        }catch (\Exception $e) {
            dump($e->getMessage());
            return [];
        }
    }

    /**
     * @param Process $process
     * @return void
     */
    private function restartProcess(Process $process)
    {
        $name = $process->name;
        $name = ucfirst($name);

        $eventName = "App\Events\Server\Restart$name"."Event";
        event(new $eventName($process));

    }

    /**
     * @param int|string $processName
     * @return array
     */
    public function getRunningProcesses(int|string $processName): array
    {
        $serverProcess = "ps -ef | grep $processName | grep -v grep | awk '{print $2}'";
        $processRunningSince = "ps -ef | grep $processName | grep -v grep | awk '{print $7}'";
        $processPath = "ps -ef | grep $processName | grep -v grep | awk '{print $8}'";
        $serverProcess2 = "ps -ef | grep $processName | grep -v grep";
        $serverProcessCommand = "ps -ef | grep $processName | grep -v grep | awk '{print $9 $10 $11}'";

        $pid = shell_exec($serverProcess);
        $pid = explode("\n", $pid);
        $pid = array_filter($pid);  // remove empty array element
        $processRunningSinceCmd = shell_exec($processRunningSince);
        // put $processRunningSinceCmd in array
        $processRunningSinceCmd = explode("\n", $processRunningSinceCmd);
        $processRunningSinceCmd = array_filter($processRunningSinceCmd);  // remove empty array element
        $processPathCmd = shell_exec($processPath);
        $processPathCmd = explode("\n", $processPathCmd);
        $processPathCmd = array_filter($processPathCmd);  // remove empty array element
        $serverProcessCommandCmd = shell_exec($serverProcessCommand);
        $serverProcessCommandCmd = explode("\n", $serverProcessCommandCmd);
        $serverProcessCommandCmd = array_filter($serverProcessCommandCmd);  // remove empty array element
        $serverProcess2Cmd = shell_exec($serverProcess2);
        $serverProcess2Cmd = explode("\n", $serverProcess2Cmd);
        $serverProcess2Cmd = array_filter($serverProcess2Cmd);  // remove empty array element

        return [
            'name' => $processName,
            'pid' => $pid ?? [],
            'processRunningSince' => $processRunningSinceCmd ?? [],
            'processPath' => $processPathCmd ??  [],
            'serverProcessCommand' => $serverProcessCommandCmd ?? [],
            'serverProcess2' => $serverProcess2Cmd ?? [],
        ];
    }

    /**
     * @param \Exception $e
     * @return void
     */
    public function getExceptionMessage(\Exception $e): void
    {
        // filter all traces containing belonging to the App\ namespace
        $trace = $e->getTrace();
        $trace = array_map(function ($trace) {
            // explode trace['class'] into class and method
            $class = explode('\\', $trace['class']);
            $class = end($class);
            return $trace['file'] . ':' . $trace['line'] . " | Class : " . $class . "  |  Method : " .$trace['function'];
        }, $trace);
        // remove all elemts contining vendor/ from the trace
        $trace = array_filter($trace, function ($trace) {
            return !str_contains($trace, 'vendor/');
        });

        dump([
            'message' => 'Process Name Not Supported. Please update your server url',
            'error' => $e->getMessage(),
            'File' => $e->getFile(),
            'Line' => $e->getLine(),
            'previous'=> $e->getPrevious(),
            'trace' => $trace,
        ]);
    }

}
