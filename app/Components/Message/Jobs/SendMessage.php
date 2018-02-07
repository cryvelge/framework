<?php

namespace App\Jobs;

use App\Components\Message\Manager;
use Log;
use Message;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!is_array($this->message)) {
            $this->message = [$this->message];
        }

        for ($i = 0, $len = count($this->message); $i < $len; $i++) {
            $result = Message::sendSync($this->message[$i]);
        }
    }
}
