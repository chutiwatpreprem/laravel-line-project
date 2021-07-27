<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Phattarachai\LineNotify\Facade\Line;
use Modules\Line\Constant\LineHookHttpResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\RouteServiceProvider;
use LINE\LINEBot;
use Illuminate\Support\Facades\Log;

class QueueJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, [
            'channelSecret' => env('LINE_BOT_CHANNEL_SECRET')
        ]);

        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('Hello Schdule QueJOB');
        $response = $bot->pushMessage(env('LINE_NOTIFY_CLIENT_ID'), $textMessageBuilder);

        echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
        Log::info('Send Line Success');
    }
}
