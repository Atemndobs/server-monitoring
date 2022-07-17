<?php

namespace App\Console\Commands;

use App\Services\ServerCheckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ServerCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:check {--all} {--host=}';

    protected ServerCheckService $serverCheckService;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->serverCheckService =  new \App\Services\ServerCheckService();;
        $host = $this->option('host');

        $all = $this->option('all');

        if (!$host && !$all) {
            $ask =$this->askWithCompletion('Please enter hostname or all',
                ['all', 'artisan', 'nest', 'vue']);
            if ($ask == 'all') {
                $all = true;
            } elseif ($ask == 'artisan') {
                $host = 'artisan';
            } elseif ($ask == 'nest') {
                $host = 'nest';
            } elseif ($ask == 'vue') {
                $host = 'vue';
            }
        }

        if ($all) {
            $this->info('Checking all servers');
            $this->pingAll();
            return 0;
        }

        $baseUrl = env('APP_DOCKER_BASE_URL') ;
        if (!$host) {
            $choice = $this->askWithCompletion('Please specify the host of app name to check', [
                'Classification Api (nest) => ' . "$baseUrl" . "3000",
                'Vue Music Api  (vue)=> ' . "$baseUrl" . "8080",
                'Laravel Backend Api (artisan) => ' . "$baseUrl" . "8899",
            ]);
            $fullHostPath = explode(' => ', $choice);
            $fullHostPath = $fullHostPath[1];
        }
        else {
            $port = $this->serverCheckService->getPort($host);

            dump([
                'host' => $host,
                'port' => $port
            ]);
            $fullHostPath = $baseUrl .  $port;
        }

        $processName = $this->getCheckProcessName($host);
        $runningProcessStatus = $this->checkRunningProcess($processName);

        dump([
            'host' => $fullHostPath,
            'processName' => $processName,
            'runningProcessStatus' => $runningProcessStatus,
        ]);
        if ($runningProcessStatus) {
            $this->info("$fullHostPath previous state was : running | Lets run another check");
            $this->ping($host);
        } else {
            $this->error("$fullHostPath is not running");
            $this->info("Killing the $processName process");
            $this->killProcess($processName);
        }

        return 0;
    }

    public function ping($host)
    {
        $baseUrl = env('APP_DOCKER_BASE_URL') ;

        if ($host === $this->getCheckProcessName($host)) {
            $port = $this->serverCheckService->getPort($host);
        }
        else {
            $port = $host;
        }
        $fullHostPath = $baseUrl . $port;
        try {
            $response = Http::get($fullHostPath);
            $this->info("$fullHostPath is alive. Status : " . $response->status());
        } catch (\Exception $e) {
            $this->error("$fullHostPath Server is not alive");
            $this->error('Triggering restart command for ' . $fullHostPath);

            $port = explode(':', $fullHostPath);
            info("$fullHostPath Server is not alive . Please check");

            $response = Http::Post(env('APP_DOCKER_BASE_URL') . "8899/api/ping",
                [
                    'port' => $port[2],
                    'app' => $fullHostPath,
                    'status' => 'down',
                ]
            );

            $this->info("$response from Laravel backend");

            return 1;
        }
    }

    public function pingAll()
    {
        $servers = [
            "3000",
            "8080",
            "8899",
        ];
        foreach ($servers as $server) {
            $this->ping($server);
        }
    }

    private function checkRunningProcess(string $name) : bool
    {
        return $this->serverCheckService->checkRunningProcess($name);
    }

    private function killProcess(bool|array|string|null $processName)
    {
        $serverProcess = "ps -ef | grep $processName | grep -v grep | awk '{print $2}'";

        $this->getRunningProcesses($processName);


        $getProcesses = shell_exec($serverProcess);
        $getProcesses = explode("\n", $getProcesses);
        foreach ($getProcesses as $process) {
            if ($process) {
                $this->output->caution('Killing process  : '. $process);
                $killProcess = "kill $process";
                shell_exec($killProcess);
            }
        }

        $processRunningSinceCmd = $this->getRunningProcesses($processName)['processRunningSince'];
        $processPathCmd = $this->getRunningProcesses($processName)['processPath'];
        $serverProcessCommandCmd = $this->getRunningProcesses($processName)['serverProcessCommand'];
        // put results in a table
        $this->table(
            ['processName', 'processRunningSince', 'processPath', 'serverProcessCommand'],
            [
                [$processName, $processRunningSinceCmd, $processPathCmd, $serverProcessCommandCmd],
            ]
        );

        return 0;
    }

    /**
     * @param bool|array|string|null $processName
     * @return array
     */
    public function getRunningProcesses(bool|array|string|null $processName): array
    {
        return $this->serverCheckService->getRunningProcesses($processName);
    }

    /**
     * @param int|string $host
     * @return string|int
     */
    public function getCheckProcessName(int|string $host): string|int
    {
        if ((int)$host > 0) {
            $processName = $this->serverCheckService->getProcessName($host);
        } else {
            $processName = $host;
        }
        return $processName;
    }

}
