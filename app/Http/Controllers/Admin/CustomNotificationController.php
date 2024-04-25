<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;

class CustomNotificationController extends CoreApiController {

    use \App\Http\Traits\PushNotification;

    public function index(Request $request) {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $notifications = \App\Model\Notification::where('title', 'like', '%' . $filter . '%')
                    ->sortable(['id' => 'desc'])
                    ->paginate(20);
            $notifications->appends(['filter' => $filter]);
        } else {
            $notifications = \App\Model\Notification::where('title', 'like', '%' . $filter . '%')
                    ->sortable(['id' => 'desc'])
                    ->paginate(20);
        }
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.custom_notification.index', ['notifications' => $notifications, 'filter' => $filter, 'users_count' => $users_count]);
    }

    public function create() {
        $users = \App\Model\User::join('user_device_detail', 'users.id', '=', 'user_device_detail.fk_user_id')
                ->select('users.id', 'users.name', 'users.country_code', 'users.mobile', 'user_device_detail.device_token', 'user_device_detail.id AS device_token_id')
                ->where('users.status', '=', 1)
                ->where('user_device_detail.device_token', '!=', null)
                ->orderBy('users.name', 'asc')
                ->orderBy('users.mobile', 'asc')
                ->get();
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.custom_notification.create', ['users' => $users, 'users_count' => $users_count]);
    }

    public function store(Request $request) {
        $title = $request->input('title');
        $body = $request->input('body');
        $dTs = [];
        $all_device_tokens = "";
        $user_ids = 'all';

        if ($request->input('all_users') && $request->input('all_users')=='1') {
            // $request->input('all_users_ids') && $request->input('all_users_ids')!='') {
                // $all_users_ids = rtrim($request->input('all_users_ids'));
                // $all_users_ids = explode(',', $all_users_ids);
                // $all_users_ids = array_filter($all_users_ids);
                // $deviceTokens = \App\Model\UserDeviceDetail::selectRaw("device_token")
                //     ->whereIn('id', $all_users_ids)
                //     ->get();

                $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                    ->select("fk_user_id","device_token")
                    ->get();
                if ($deviceTokens) {
                    foreach ($deviceTokens as $deviceToken) {
                        $dTs[] = $deviceToken->device_token;
                        $all_device_tokens .= $deviceToken->device_token.',';
                        \Log::info('Notifications title (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                    }
                }
                $user_ids = 'all';
        }
        else if ($request->input('all_english_users') && $request->input('all_english_users')=='1') {
                $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                    ->select("fk_user_id","device_token")
                    ->where("lang_preference","en")
                    ->get();
                if ($deviceTokens) {
                    foreach ($deviceTokens as $deviceToken) {
                        $dTs[] = $deviceToken->device_token;
                        $all_device_tokens .= $deviceToken->device_token.',';
                        \Log::info('Notifications title English (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                    }
                }
                $user_ids = 'all_english_users';
        }
        else if ($request->input('all_arabic_users') && $request->input('all_arabic_users')=='1') {
                $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                    ->select("fk_user_id","device_token")
                    ->where("lang_preference","ar")
                    ->get();
                if ($deviceTokens) {
                    foreach ($deviceTokens as $deviceToken) {
                        $dTs[] = $deviceToken->device_token;
                        $all_device_tokens .= $deviceToken->device_token.',';
                        \Log::info('Notifications title Arabic (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                    }
                }
                $user_ids = 'all_arabic_users';
        }
        else if ($request->input('users')) {
            // $deviceTokens = \App\Model\UserDeviceDetail::selectRaw("GROUP_CONCAT(device_token) as deviceTokens")
            //         ->whereIn('fk_user_id', $request->input('users'))
            //         ->first();
            // $all_device_tokens = $deviceTokens->deviceTokens;
            // $dTs = explode(',', $all_device_tokens);
            
            $deviceTokens = \App\Model\UserDeviceDetail::select("fk_user_id","device_token")
                    ->whereIn('fk_user_id', $request->input('users'))
                    ->get();
            if ($deviceTokens) {
                foreach ($deviceTokens as $deviceToken) {
                    $dTs[] = $deviceToken->device_token;
                    $all_device_tokens .= $deviceToken->device_token.',';
                    \Log::info('Notifications title: '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                }
            }
            $user_ids = $request->input('users') ? implode(',', $request->input('users')) : ''; 
        }
        else if($request->input('users_mobile') && $request->input('users_mobile')=='1'){
            $path = "/stock_files/";
            $file = $this->uploadFile($request, 'user_mobile_csv', $path);

            $users_mobile_numbers = csvToArray(public_path('/stock_files/') . $file);
            $users_mobile_numbers_arr = [];
            foreach ($users_mobile_numbers as $key => $value) {
                $users_mobile_number = \App\Model\User::where('mobile', $value[0])->first();
                if($users_mobile_number){
                    $users_mobile_numbers_arr[] = $users_mobile_number->id;
                }
            }
            $deviceTokens = \App\Model\UserDeviceDetail::select("fk_user_id","device_token")
                    ->whereIn('fk_user_id', $users_mobile_numbers_arr)
                    ->get();

            if ($deviceTokens) {
                foreach ($deviceTokens as $deviceToken) {
                    $dTs[] = $deviceToken->device_token;
                    $all_device_tokens .= $deviceToken->device_token.',';
                    \Log::info('Notifications title: '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                }
            }

            $user_ids = 'all_users_from_csv_file';
        }
        $data = [];
        $type = '1';
        $dTs_chunks = array_chunk($dTs,500);
        foreach ($dTs_chunks as $dTs_chunk) {
            $res = $this->send_push_notification_to_multiple_user($dTs_chunk, $type, $title, $body, $data);
        }

        if ($res) {
            \App\Model\Notification::create([
                'title' => $title,
                'body' => $body,
                'userIds' => $user_ids
            ]);
            return redirect('admin/custom_notifications')->with('success', 'Sent successfully!');
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function edit(Request $request, $id = null) {
        $id = base64url_decode($id);

        $notification = \App\Model\Notification::find($id);

        $users = \App\Model\User::where(['status' => 1])
                ->orderBy('name', 'asc')
                ->orderBy('mobile', 'asc')
                ->get();
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.custom_notification.edit', ['users' => $users, 'notification' => $notification, 'users_count' => $users_count]);
    }

    public function update(Request $request, $id = null) {
        $id = base64url_decode($id);

        $title = $request->input('title');
        $body = $request->input('body');
        $dTs = [];
        $all_device_tokens = "";
        $user_ids = 'all';

        if ($request->input('all_users') && $request->input('all_users')=='1') {
            // $request->input('all_users_ids') && $request->input('all_users_ids')!='') {
                // $all_users_ids = rtrim($request->input('all_users_ids'));
                // $all_users_ids = explode(',', $all_users_ids);
                // $all_users_ids = array_filter($all_users_ids);
                // $deviceTokens = \App\Model\UserDeviceDetail::selectRaw("device_token")
                //     ->whereIn('id', $all_users_ids)
                //     ->get();

                $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                    ->select("fk_user_id","device_token")
                    ->get();
                if ($deviceTokens) {
                    foreach ($deviceTokens as $deviceToken) {
                        $dTs[] = $deviceToken->device_token;
                        $all_device_tokens .= $deviceToken->device_token.',';
                        \Log::info('Notifications title (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                    }
                }
                $user_ids = 'all';
        }
        else if ($request->input('all_english_users') && $request->input('all_english_users')=='1') {
                $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                    ->select("fk_user_id","device_token")
                    ->where("lang_preference","en")
                    ->get();
                if ($deviceTokens) {
                    foreach ($deviceTokens as $deviceToken) {
                        $dTs[] = $deviceToken->device_token;
                        $all_device_tokens .= $deviceToken->device_token.',';
                        \Log::info('Notifications title English (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                    }
                }
                $user_ids = 'all_english_users';
        }
        else if ($request->input('all_arabic_users') && $request->input('all_arabic_users')=='1') {
                $deviceTokens = \App\Model\UserDeviceDetail::join('users', 'user_device_detail.fk_user_id', '=', 'users.id')
                    ->select("fk_user_id","device_token")
                    ->where("lang_preference","ar")
                    ->get();
                if ($deviceTokens) {
                    foreach ($deviceTokens as $deviceToken) {
                        $dTs[] = $deviceToken->device_token;
                        $all_device_tokens .= $deviceToken->device_token.',';
                        \Log::info('Notifications title Arabic (All): '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                    }
                }
                $user_ids = 'all_arabic_users';
        }
        else if ($request->input('users')) {
            // $deviceTokens = \App\Model\UserDeviceDetail::selectRaw("GROUP_CONCAT(device_token) as deviceTokens")
            //         ->whereIn('fk_user_id', $request->input('users'))
            //         ->first();
            // $all_device_tokens = $deviceTokens->deviceTokens;
            // $dTs = explode(',', $all_device_tokens);
            
            $deviceTokens = \App\Model\UserDeviceDetail::select("fk_user_id","device_token")
                    ->whereIn('fk_user_id', $request->input('users'))
                    ->get();
            if ($deviceTokens) {
                foreach ($deviceTokens as $deviceToken) {
                    $dTs[] = $deviceToken->device_token;
                    $all_device_tokens .= $deviceToken->device_token.',';
                    \Log::info('Notifications title: '.$title.' sending to ID: '.$deviceToken->fk_user_id.' token: '.$deviceToken->device_token.' ');
                }
            }
            $user_ids = $request->input('users') ? implode(',', $request->input('users')) : ''; 
        }
        $data = [];
        $type = '1';
        $dTs_chunks = array_chunk($dTs,500);
        foreach ($dTs_chunks as $dTs_chunk) {
            $res = $this->send_push_notification_to_multiple_user($dTs_chunk, $type, $title, $body, $data);
        }

        if ($res) {
            \App\Model\Notification::create([
                'title' => $title,
                'body' => $body,
                'userIds' => $user_ids
            ]);
            return redirect('admin/custom_notifications')->with('success', 'Sent successfully!');
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function show($id = null) {
        $id = base64url_decode($id);

        $notification = \App\Model\Notification::find($id);
        if ($notification) {
            $notification->users = $notification->getUsers($notification->userIds);

            return view('admin.custom_notification.show', [
                'notification' => $notification
            ]);
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function destroy($id = null) {
        $id = base64url_decode($id);
        $notification = \App\Model\Notification::find($id)->delete();
        if ($notification) {
            return redirect('admin/custom_notifications')->with('success', 'Deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Something went wrong!');
        }
    }

    public function sheduled_notifications(Request $request) {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $notifications = \App\Model\SheduledNotification::where('title', 'like', '%' . $filter . '%')
                    ->sortable(['id' => 'desc'])
                    ->paginate(20);
            $notifications->appends(['filter' => $filter]);
        } else {
            $notifications = \App\Model\SheduledNotification::where('title', 'like', '%' . $filter . '%')
                    ->sortable(['id' => 'desc'])
                    ->paginate(20);
        }
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.sheduled_notification.index', ['notifications' => $notifications, 'filter' => $filter, 'users_count' => $users_count]);
    }

    public function sheduled_notification_create() {
        $users = \App\Model\User::join('user_device_detail', 'users.id', '=', 'user_device_detail.fk_user_id')
                ->select('users.id', 'users.name', 'users.country_code', 'users.mobile', 'user_device_detail.device_token', 'user_device_detail.id AS device_token_id')
                ->where('users.status', '=', 1)
                ->where('user_device_detail.device_token', '!=', null)
                ->orderBy('users.name', 'asc')
                ->orderBy('users.mobile', 'asc')
                ->get();
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.sheduled_notification.create', ['users' => $users, 'users_count' => $users_count]);
    }

    public function sheduled_notification_store(Request $request) {
        $title = $request->input('title');
        $body = $request->input('body');
        $shedule_date = $request->input('shedule_date');
        $shedule_time = $request->input('shedule_date').' '.$request->input('shedule_time');

        $user_ids = '';
        if ($request->input('all_users') && $request->input('all_users')=='1') {
            $user_ids = 'all';
        }
        else if ($request->input('all_english_users') && $request->input('all_english_users')=='1') {
            $user_ids = 'all_english_users';
        }
        else if ($request->input('all_arabic_users') && $request->input('all_arabic_users')=='1') {
            $user_ids = 'all_arabic_users';
        }
        else if ($request->input('users')) {
            $user_ids = implode(',', $request->input('users')); 
        }

        if ($user_ids!='') {
            \App\Model\SheduledNotification::create([
                'title' => $title,
                'body' => $body,
                'userIds' => $user_ids,
                'shedule_date' => $shedule_date,
                'shedule_time' => $shedule_time,
                'sent' => 0
            ]);
            return redirect('admin/sheduled_notification/create')->with('success', 'Sheduled notification successfully!');
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function sheduled_notification_edit(Request $request, $id = null) {
        $id = base64url_decode($id);

        $notification = \App\Model\SheduledNotification::find($id);

        $users = \App\Model\User::where(['status' => 1])
                ->orderBy('name', 'asc')
                ->orderBy('mobile', 'asc')
                ->get();
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.sheduled_notification.edit', ['users' => $users, 'notification' => $notification, 'users_count' => $users_count]);
    }

    public function sheduled_notification_update(Request $request, $id = null) {
        $id = base64url_decode($id);

        $title = $request->input('title');
        $body = $request->input('body');
        $shedule_date = $request->input('shedule_date');
        $shedule_time = $request->input('shedule_date').' '.$request->input('shedule_time');

        $user_ids = '';
        if ($request->input('all_users') && $request->input('all_users')=='1') {
            $user_ids = 'all';
        }
        else if ($request->input('all_english_users') && $request->input('all_english_users')=='1') {
            $user_ids = 'all_english_users';
        }
        else if ($request->input('all_arabic_users') && $request->input('all_arabic_users')=='1') {
            $user_ids = 'all_arabic_users';
        }
        else if ($request->input('users')) {
            $user_ids = implode(',', $request->input('users')); 
        }

        if ($user_ids!='' && $id!='') {
            \App\Model\SheduledNotification::find($id)->update([
                'title' => $title,
                'body' => $body,
                'userIds' => $user_ids,
                'shedule_date' => $shedule_date,
                'shedule_time' => $shedule_time
            ]);
            return redirect('admin/sheduled_notifications')->with('success', 'Sent successfully!');
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function sheduled_notification_resend(Request $request, $id = null) {
        $id = base64url_decode($id);

        $notification = \App\Model\SheduledNotification::find($id);

        $users = \App\Model\User::where(['status' => 1])
                ->orderBy('name', 'asc')
                ->orderBy('mobile', 'asc')
                ->get();
        $users_count = \App\Model\User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();
        
        return view('admin.sheduled_notification.resend', ['users' => $users, 'notification' => $notification, 'users_count' => $users_count]);
    }

    public function sheduled_notification_resend_update(Request $request, $id = null) {
        $id = base64url_decode($id);

        $title = $request->input('title');
        $body = $request->input('body');
        $shedule_date = $request->input('shedule_date');
        $shedule_time = $request->input('shedule_date').' '.$request->input('shedule_time');

        $user_ids = '';
        if ($request->input('all_users') && $request->input('all_users')=='1') {
            $user_ids = 'all';
        }
        else if ($request->input('all_english_users') && $request->input('all_english_users')=='1') {
            $user_ids = 'all_english_users';
        }
        else if ($request->input('all_arabic_users') && $request->input('all_arabic_users')=='1') {
            $user_ids = 'all_arabic_users';
        }
        else if ($request->input('users')) {
            $user_ids = $request->input('users'); 
        }

        if ($user_ids!='') {
            \App\Model\SheduledNotification::create([
                'title' => $title,
                'body' => $body,
                'userIds' => $user_ids,
                'shedule_date' => $shedule_date,
                'shedule_time' => $shedule_time,
                'sent' => 0
            ]);
            return redirect('admin/sheduled_notifications')->with('success', 'Sent successfully!');
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function sheduled_notification_show($id = null) {
        $id = base64url_decode($id);

        $notification = \App\Model\SheduledNotification::find($id);
        if ($notification) {
            $notification->users = $notification->getUsers($notification->userIds);

            return view('admin.sheduled_notification.show', [
                'notification' => $notification
            ]);
        }
        return back()->withInput()->with('error', 'Something went wrong!');
    }

    public function sheduled_notification_destroy($id = null) {
        $id = base64url_decode($id);
        $notification = \App\Model\SheduledNotification::find($id)->delete();
        if ($notification) {
            return redirect('admin/sheduled_notifications')->with('success', 'Deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Something went wrong!');
        }
    }

}
