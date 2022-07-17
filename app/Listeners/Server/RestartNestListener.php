<?php

namespace App\Listeners\Server;

use App\Models\Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class RestartNestListener
{

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $process = $event->process;
        if ($process) {
            dump('Delete Process FromDB Nest');
            $process->delete();
        }
        // inform laravel backend that process has been deleted
        Http::post(env('APP_DOCKER_BASE_URL').'/api/ping', [
            'process_id' => $event->process->id,
            'status' => 'deleted',
            'name' => 'nest',
            'message' => 'Nest Process Deleted',
        ]);
    }
}
