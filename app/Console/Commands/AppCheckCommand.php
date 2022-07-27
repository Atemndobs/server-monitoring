<?php

namespace App\Console\Commands;

use App\Events\Server\RestartNestEvent;
use App\Events\Server\RestartVueEvent;
use App\Jobs\Server\RestartArtisanJob;
use App\Jobs\Server\RestartNestJob;
use App\Jobs\Server\RestartVueJob;
use Illuminate\Console\Command;

class AppCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:restart {--app=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $app = $this->option('app');

        if ((int)($app)) {
            $app = $this->getAppName($app);
        }

        if (!$app) {
            $this->error('Please specify the app to restart');
            return 1;
        }
        $this->info('Checking app ' . $app);

        if ($app=="artisan"){
            $this->checkArtisan($app);
        }else{
            dump($app);
            $this->getNodeProcess($app);
        }
        return 0;
    }

    /**
     * @param int $app
     * @return false|int|string
     */
    private function getAppName(int $app): bool|int|string
    {
        $servers = [
            'nest' =>  "3000",
            'vue' =>  "8080",
            'artisan' =>  "8899",
        ];

        return array_search($app, $servers);
    }

    /**
     * @param string $nodejsProcess
     * @return void
     */
    public function getNodeProcess(string $nodejsProcess): void
    {
        $node = shell_exec('pgrep node');
        $processes = shell_exec("ps -aef | grep $nodejsProcess | grep node");

        $nodeProcesses = explode("\n", $node);
        $systems = explode("\n", $processes);

        $candidates = [];

        foreach ($systems as $system) {
            if ($system == null) {
                continue;
            }

            $this->output->info($system);
            $systemProcesses = explode(' ', $system);
            foreach ($systemProcesses as $ky => $str) {
                if ($str == null) {
                    unset($systemProcesses[$ky]);
                }
            }
            $systemProcesses = array_values($systemProcesses);
            $candidates[] = $systemProcesses[1];
        }

       // $deleted = implode(' ', $candidates);
        //shell_exec("kill  $deleted");
       //

        if ($nodeProcesses == 'vue') {
            //dispatch(new RestartVueJob());
            $this->output->info(" Restarting Vuejs on port 8080");

            RestartVueEvent::dispatch($nodejsProcess);
            dump('EOL___________________ ');

        }
        if ($nodeProcesses == 'nest') {
            //dispatch(new RestartNestJob());
            $this->output->info(" Restarting Nestjs on port 3000");
            RestartNestEvent::dispatch($nodejsProcess);
            dump('EOL___________________');
        }


    }

    public function checkArtisan(string $artisan)
    {
        $processes = shell_exec("ps -aef | grep $artisan | grep php");
        //dispatch(new RestartArtisanJob());
    }

}
