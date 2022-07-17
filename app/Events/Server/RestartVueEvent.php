<?php

namespace App\Events\Server;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestartVueEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $app;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $app)
    {
        $this->app = $app;
        info('Restarting VUE JS __________________________________________________________'. $app);
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
