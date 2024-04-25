<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class SendSheduledPushNotifications extends Command
{
    use \App\Http\Traits\PushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Send:SheduledPushNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To send sheduled push notifications';

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
            $sheduled_notifications = \App\Model\SheduledNotification::where(['shedule_time' => $time_now, 'sent' => 0, 'deleted' => 0])
                    ->orderBy('id','asc')->get();
            if ($sheduled_notifications->first()) {
                foreach ($sheduled_notifications as $sheduled_notification) {
                    $title = $sheduled_notification->title;
                    $body = $sheduled_notification->body;
                    $user_ids = $sheduled_notification->userIds;
                    $dTs = [];
                    $all_device_tokens = "";
                    // Send Push Notifications
                    if ($user_ids=='all') {
                        $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                            ->select("fk_user_id","device_token")
                            ->from(DB::raw('(SELECT * FROM user_device_detail ORDER BY updated_at DESC) user_device_detail'))
                            ->groupBy("device_token")
                            ->get();
                        if ($deviceTokens) {
                            foreach ($deviceTokens as $deviceToken) {
                                $dTs[] = $deviceToken->device_token;
                                $all_device_tokens .= $deviceToken->device_token.',';
                                \Log::info('Notifications title (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                            }
                        }
                    }
                    else if ($user_ids=='all_english_users') {
                        $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                            ->select("fk_user_id","device_token")
                            ->from(DB::raw('(SELECT * FROM user_device_detail ORDER BY updated_at DESC) user_device_detail'))
                            ->where("lang_preference","en")
                            ->groupBy("device_token")
                            ->get();
                        if ($deviceTokens) {
                            foreach ($deviceTokens as $deviceToken) {
                                $dTs[] = $deviceToken->device_token;
                                $all_device_tokens .= $deviceToken->device_token.',';
                                \Log::info('Notifications title English (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                            }
                        }
                    }
                    else if ($user_ids=='all_arabic_users') {
                        $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                            ->select("fk_user_id","device_token")
                            ->from(DB::raw('(SELECT * FROM user_device_detail ORDER BY updated_at DESC) user_device_detail'))
                            ->where("lang_preference","ar")
                            ->groupBy("device_token")
                            ->get();
                        if ($deviceTokens) {
                            foreach ($deviceTokens as $deviceToken) {
                                $dTs[] = $deviceToken->device_token;
                                $all_device_tokens .= $deviceToken->device_token.',';
                                \Log::info('Notifications title Arabic (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                            }
                        }
                    }
                    else {
                        $user_ids_arr = explode(',',$user_ids);
                        $deviceTokens = \App\Model\UserDeviceDetail::select("fk_user_id","device_token")
                                ->whereIn('fk_user_id', $user_ids_arr)
                                ->get();
                        if ($deviceTokens) {
                            foreach ($deviceTokens as $deviceToken) {
                                $dTs[] = $deviceToken->device_token;
                                $all_device_tokens .= $deviceToken->device_token.',';
                                \Log::info('Notifications title: '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                            }
                        }
                    }
                    $data = [];
                    $type = '1';
                    $dTs_chunks = array_chunk($dTs,500);
                    foreach ($dTs_chunks as $key => $dTs_chunk) {
                        $res = $this->send_push_notification_to_multiple_user($dTs_chunk, $type, $title, $body, $data);
                        \Log::info('Sent sheduled push notification (ID: '.$sheduled_notification->id.') to '.$user_ids.' - Chunk no: '.$key.' - '.$res);
                    }
                    if($res) {
                        \App\Model\SheduledNotification::find($sheduled_notification->id)->update(['sent'=>1]);
                    }
                    \Log::info('Sent sheduled push notification (ID: '.$sheduled_notification->id.') to '.$user_ids);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Sending sheduled push notification: ". $e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
        }
        
        return 0;
    }
}
