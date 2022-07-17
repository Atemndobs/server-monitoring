<?php

namespace App\Listeners\Server;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RestartVueListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        dump('RestartVueListener');
        $this->restartVue();
    }

    public function restartVue()
    {
        //"cd ~/sites/curator/music-player && npm run serve --fix &"
        $cmd =  "cd ~/sites/curator/music-player && npm run serve --fix & disown";
        $shell = shell_exec($cmd);
        info($shell);
        return $shell;
    }
}
