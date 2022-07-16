<?php

namespace App\Jobs\Server;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestartArtisanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->restartArtisan();
    }

    public function restartArtisan()
    {
        // cd /home/atem/sites/curator/laravel && vendor/bin/sail
        // sail artisan websockets:serve
        // sail up --build -d && soc
        $cmd =  "cd /home/atem/sites/curator/laravel && vendor/bin/sail up --build && sail artisan websockets:serve & disown";
        $shell = shell_exec($cmd);
        info($shell);
        return $shell;
    }
}
