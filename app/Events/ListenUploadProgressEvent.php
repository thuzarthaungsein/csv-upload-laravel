<?php

namespace App\Events;

use App\Models\CsvFile;
use App\Models\Progress;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListenUploadProgressEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        private CsvFile $file,
        private Progress $progress
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('public.upload');
    }


    public function broadcastAs()
    {
        return 'csv-upload';
    }


    public function broadcastWith()
    {
        return [
            'progress' => $this->progress ?? null,
            'file' => $this->file,
        ];
    }
}
