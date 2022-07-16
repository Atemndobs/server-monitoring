<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ServerCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:check {--all=null} {--host=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping server and check if it is alive. If not trigger restart command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $all = $this->option('all');
        if ($all) {
            $this->info('Checking all servers');
            $this->pingAll();
            return 0;
        }

        $host = $this->option('host');
        $baseUrl = env('APP_DOCKER_BASE_URL') ;
        if (!$host) {
            $choice = $this->askWithCompletion('Please specify the host of app name to check', [
                'Classification Api  => ' . "$baseUrl" . "3000",
                'Vue Music Api  => ' . "$baseUrl" . "8080",
                'Laravel Backend Api => ' . "$baseUrl" . "8899",
            ]);
            $fullHostPath = explode(' => ', $choice);
            $fullHostPath = $fullHostPath[1];
        }
        else {
            $fullHostPath = $host;
        }
        $this->ping($fullHostPath);

        return 0;
    }

    public function ping($fullHostPath)
    {
        try {
            $response = Http::get($fullHostPath);
            $this->info("$fullHostPath is alive. Status : " . $response->status());
        } catch (\Exception $e) {
            $this->error("$fullHostPath Server is not alive");
            $this->error('Triggering restart command for ' . $fullHostPath);

            $port = explode(':', $fullHostPath);
            $this->call("server:restart", ['--app' => $port[2]]);

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
            env('APP_DOCKER_BASE_URL') . "3000",
            env('APP_DOCKER_BASE_URL') . "8080",
            env('APP_DOCKER_BASE_URL') . "8899",
        ];
        foreach ($servers as $server) {
            $this->ping($server);
        }
    }
}
