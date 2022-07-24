<?php

namespace App\Console\Commands;

use App\Models\Process;
use Illuminate\Console\Command;

class WatchVueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:vue';

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
        $processes = Process::query()->where('name', 'vue')->get();
        if ($processes->count() == 0) {
            $this->info('No Vue Processes Found');
            $this->warn('Starting Vue Process');

            // check Vue process on server
            $vueProcess = shell_exec('ps -ef | grep vue | grep -v grep');
            $vueProcess = explode("\n", $vueProcess);
            // remove empty array element
            $vueProcess = array_filter($vueProcess);  // remove empty array element

            if (count($vueProcess) == 1 && str_contains($vueProcess[0], 'php artisan watch')) {
                $this->info('Only This Process Running, So No Need to Start Vue');
                dump(shell_exec("pwd"));
                shell_exec("cd ~/sites/curator/music-player && npm run serve --fix");
            }else{
                $this->info('Other Vue Processes Running on Server : Please check');
                // kill all Vue process on server
                $vue = shell_exec('ps -ef | grep vue | grep -v grep | awk \'{print $2}\'');
                $vue = explode("\n", $vue);
                $vue = array_filter($vue);  // remove empty array element
                foreach ($vue as $pid) {
                    shell_exec("kill -9 $pid");
                }
                shell_exec("cd ~/sites/curator/music-player && npm run serve --fix");
                return 1;
            }
        }
        $this->info('Vue Processes Found');
        // output found processes in table format
        $this->table(['ID', 'Name', 'Command', 'PID', 'Status'], $processes->map(function ($process) {
            return [
                $process->id,
                $process->name,
                $process->command,
                $process->pid,
                $process->status,
            ];
        }));
        return 0;
    }
}
