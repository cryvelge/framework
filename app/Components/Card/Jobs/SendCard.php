<?php

namespace App\Components\Card\Jobs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Components\Card\Card;
use EasyWeChat;
use Message;
use Splunk;
use Log;

class SendCard implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    public $card;
    public $uid;
    public $time;

    public function __construct(Card $card, $uid, $time = null)
    {
        if(!is_null($time)) {
            $this->time = $time;
        }
        $this->card = $card;
        $this->uid = $uid;
        $this->onQueue('card');
    }

    public function handle()
    {
        $mediaId = $this->upload();
        if(!is_null($mediaId)) {
            $this->send($mediaId);
        }
    }

    public function upload()
    {
        $client = new Client();
        $image = $this->card->create();
        $accessToken = EasyWeChat::access_token()->getToken();
        $random = Carbon::now()->timestamp.random_int(100,999);
        $response = $client->post('https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$accessToken."&type=image", [
            'multipart' => [
                [
                    'name' => 'media',
                    'filename' => $random.".jpeg",
                    'contents' => $image->getImageBlob(),
                ],
            ]
        ])->getBody()->getContents();
        $response = json_decode($response);

        Splunk::log('card_upload', (array) $response);
        $this->card->clear();
        return $response->media_id?? null;
    }

    public function send($mediaId)
    {
        $message = Message::generate([
            'type' => 'image',
            'content' => $mediaId,
        ]);
        $message->setTopic('wechat');
        $message->setUserId($this->uid);
        if(is_null($this->time)) {
            $message->send();
        }else{
            $message->sendAt($this->time);
        }

    }
}
