<?php

namespace App\Console\Commands;

use App\Models\Process;
use Illuminate\Console\Command;

class WatchNestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:nest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if Nest Process Is Running (from DB) Then Start if there is not related Nest process running on server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $processes = Process::query()->where('name', 'nest')->get();
        if ($processes->count() == 0) {
            $this->info('No Nest Processes Found');
            $this->warn('Starting Nest Process');

            // check nest process on server
            $nestProcess = shell_exec('ps -ef | grep nest | grep -v grep');
            $nestProcess = explode("\n", $nestProcess);
            // remove empty array element
            $nestProcess = array_filter($nestProcess);  // remove empty array element
            if (count($nestProcess) == 1 && str_contains($nestProcess[0], 'php artisan watch')) {
                $this->info('Only This Process Running, So No Need to Start Nest');
                dump(shell_exec("pwd"));
                shell_exec("cd ~/sites/curator/nested &&  /usr/local/bin/npm run start");
            }else{
                $this->info('Other Nest Processes Running on Server : Please check');
                // kill all nest process on server
                $nest = shell_exec('ps -ef | grep nest | grep -v grep | awk \'{print $2}\'');
                $nest = explode("\n", $nest);
                $nest = array_filter($nest);  // remove empty array element
                foreach ($nest as $pid) {
                    shell_exec("kill -9 $pid");
                }
                shell_exec("cd ~/sites/curator/nested &&  /usr/local/bin/npm run start");
                return 1;
            }
        }
        $this->info('Nest Processes Found');
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
