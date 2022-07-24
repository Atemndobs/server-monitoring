<?php

namespace App\Listeners\Server;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class RestartVueListener
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
            dump("Delete Process FromDB $process->name");
            $process->delete();
        }
        try {
            Http::post(env('APP_DOCKER_BASE_URL').'/api/ping', [
                'process_id' => $event->process->id,
                'status' => 'deleted',
                'name' => $process->name,
                'message' => 'Nest Process Deleted',
            ]);
        }catch (\Exception $e) {
            dump($e->getMessage());
        }
    }
}
