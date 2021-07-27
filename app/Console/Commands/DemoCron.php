<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Phattarachai\LineNotify\Facade\Line;
use Modules\Line\Constant\LineHookHttpResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\RouteServiceProvider;
use LINE\LINEBot;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, [
            'channelSecret' => env('LINE_BOT_CHANNEL_SECRET')
        ]);

        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('Hello Schdule2');
        $response = $bot->pushMessage(env('LINE_NOTIFY_CLIENT_ID'), $textMessageBuilder);

        echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
        Log::info($response->getHTTPStatus() . ' ' . $response->getRawBody());
    }
}
