<?php

namespace App\Components\Message\Jobs;

use Log;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Components\Message\Store\MessageList;
use App\Components\Message\Store\MessageBody;
use App\Components\Message\Manager;
use App\Components\Message\Scheduler;
use App\Components\Message\MessageGroup;
use App\Components\Message\Message;

use Message as MessageFacade;

class SendScheduleMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 分钟数
    protected $minute = -1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($minute)
    {
        $this->minute = $minute;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $id = MessageList::pop($this->minute);
        if ($id == null || $id == false) {
            return;
        }
        $raw = MessageBody::get($id);
        $raw_array = unserialize($raw);

        $topic = $raw_array['topic'];
        if ($topic == null) {
            return;
        }

        if ($topic == '_group') {
            $message = MessageGroup::inflate($raw_array);
        } else {
            $message = Message::inflate($raw_array);
        }
        // 发送消息
        MessageFacade::sendSync($message);

        // 将下一个job加入到队列中
        Scheduler::sendScheduleMessage($this->minute);
    }
}
