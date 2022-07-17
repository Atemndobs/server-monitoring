<?php

namespace App\Listeners\Server;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RestartLaravelListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
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
