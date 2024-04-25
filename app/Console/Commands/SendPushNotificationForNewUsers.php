<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SendPushNotificationForNewUsers extends Command
{
    use \App\Http\Traits\PushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Send:PushNotificationForNewUsers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To send automated push notifications for those who joined recently';

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
        try {
            // Get all sheduled notifications of the minute
            $time_now = date('Y-m-d  H:i');
            $time_start = date('Y-m-d H:i:s',strtotime("-20 minutes"));
            $time_end = date('Y-m-d H:i:s',strtotime("-10 minutes"));
            \Log::info('Send:PushNotificationForNewUsers running - checking from: '.$time_start.' to:'.$time_end);
            
            // Send Push Notifications To New Users
            $deviceTokens = \App\Model\User::join('user_device_detail', 'users.id', '=', 'user_device_detail.fk_user_id')
                ->select("user_device_detail.fk_user_id","users.name","user_device_detail.device_token")
                ->where("users.created_at",">=",$time_start)
                ->where("users.created_at","<",$time_end)
                ->groupBy("user_device_detail.fk_user_id")
                ->get();
            if ($deviceTokens) {
                foreach ($deviceTokens as $deviceToken) {
                    $title = "Welcome to Jeeb❤️";
                    $body = "enjoy FREE DELIVERY and thousands of products! 🛒";
                    $data = [];
                    $type = '1';
                    \Log::info('Send:PushNotificationForNewUsers sending to ID: '.$deviceToken->fk_user_id.', name: '.$deviceToken->name.' token: '.$deviceToken->device_token.' ');
                    $title = $deviceToken->name!='' ? ucwords($deviceToken->name).", welcome to Jeeb❤️" : $title;
                    $res = $this->send_push_notification_to_user($deviceToken->device_token, $type, $title, $body, $data);
                    \Log::info('Send:PushNotificationForNewUsers sending to ID: '.$deviceToken->fk_user_id.', name: '.$deviceToken->name.' token: '.$deviceToken->device_token.' - '.$title.' - '.$res);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Send:PushNotificationForNewUsers ". $e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
        }
        
        return 0;
    }
}
