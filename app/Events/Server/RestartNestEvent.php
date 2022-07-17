<?php

namespace App\Events\Server;

use App\Models\Process;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestartNestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Process $process;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
        info('Restarting Nest__________________________________________________________');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
