<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SendPushNotificationForUsersCart extends Command
{
    use \App\Http\Traits\PushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Send:PushNotificationForUsersCart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To send automated push notifications for those who have items in their cart';

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
            $time_start = date('Y-m-d H:i:s',strtotime("-60 minutes"));
            $time_end = date('Y-m-d H:i:s',strtotime("-30 minutes"));
            \Log::info('Send:PushNotificationForUsersCart running - checking from: '.$time_start.' to:'.$time_end);
            
            // Send Push Notifications To New Users
            $deviceTokens = \App\Model\UserCart::join('users', 'user_cart.fk_user_id', '=', 'users.id')
                ->join('user_device_detail', 'user_cart.fk_user_id', '=', 'user_device_detail.fk_user_id')
                ->select("user_device_detail.fk_user_id","users.name","user_device_detail.device_token")
                ->where("user_cart.updated_at",">=",$time_start)
                ->where("user_cart.updated_at","<",$time_end)
                ->groupBy("user_cart.fk_user_id")
                ->get();
            if ($deviceTokens) {
                foreach ($deviceTokens as $deviceToken) {
                    $title = "Can we help?";
                    $body = "You left some items in the cart, go and save yourself the time!";
                    $data = [];
                    $type = '1';
                    // Check with last updated cart
                    $user_updated_cart_in_latest_30minutes = \App\Model\UserCart::where('fk_user_id','=',$deviceToken->fk_user_id)
                                                            ->where("user_cart.updated_at",">=",$time_end)->first();
                    if (!$user_updated_cart_in_latest_30minutes) {
                        \Log::info('Send:PushNotificationForUsersCart: sending to ID: '.$deviceToken->fk_user_id.', name: '.$deviceToken->name.' token: '.$deviceToken->device_token.' ');
                        $title = $deviceToken->name!='' ? ucwords($deviceToken->name).", can we help?" : $title;
                        $res = $this->send_push_notification_to_user($deviceToken->device_token, $type, $title, $body, $data);
                        \Log::info('Send:PushNotificationForUsersCart: sending to ID: '.$deviceToken->fk_user_id.', name: '.$deviceToken->name.' token: '.$deviceToken->device_token.' - '.$title.' - '.$res);
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error("Send:PushNotificationForUsersCart ". $e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
        }
        
        return 0;
    }
}
