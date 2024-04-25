<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\CustomerSupport;
use App\Model\Order;
use App\Model\StorekeeperProduct;
use App\Model\TechnicalSupport;
use App\Model\TechnicalSupportChat;
use App\Model\TechnicalSupportProduct;

class SupportController extends CoreApiController
{

    use \App\Http\Traits\PushNotification;

    public function technical_support(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $support = CustomerSupport::where('subject', 'like', '%' . $filter . '%')
                ->orWhere('description', 'like', '%' . $filter . '%')
                ->sortable(['id', 'desc'])
                ->paginate(20);
        } else {
            $support = CustomerSupport::sortable(['id', 'desc'])
                ->paginate(20);
        }
        $support->appends(['filter' => $filter]);

        return view('admin.support.technical_support_tickets', ['support' => $support, 'filter' => $filter]);
    }

    protected function technical_support_detail(Request $request, $id)
    {
        $id = base64url_decode($id);
        $support = CustomerSupport::find($id);

        $messages = \App\Model\CustomerSupportChat::where(['fk_support_id' => $id])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.support.technical_support_detail', [
            'support' => $support,
            'messages' => $messages
        ]);
    }

    protected function send_message(Request $request)
    {
        $update = \App\Model\CustomerSupportChat::create([
            'fk_support_id' => $request->input('support_id'),
            'fk_user_id' => $request->input('user_id'),
            'from' => 'jeeb',
            'message' => $request->input('chat_content'),
            'type' => 'text'
        ]);
        if ($update) {
            $ret = [
                'id' => $update->id,
                'created_at' => date('H:i A | d-m-Y', strtotime($update->created_at))
            ];

            //sending push notification and in-app notification
            $data = [];
            //sending push notification and in-app notification
            $data = [
                'ticket_id' => $request->input('support_id'),
                'order_id' => $request->input('order_id'),
                'message_type' => 2,
                'message' => $request->input('chat_content'),
                'type' => 'text'
            ];
            $this->send_inapp_notification_to_user(
                $request->input('user_id'),
                "New support message",
                "رسالة دعم جديدة",
                $request->input('chat_content'),
                "لقد تلقيت رسالة دعم جديدة",
                2, //for technical support chat
                $data
            );

            $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $request->input('user_id')])->first();
            if ($user_device) {
                $type = 2; //for technical support chat
                $title = "New support message";
                $body = $request->input('chat_content');

                $this->send_push_notification_to_user($user_device->device_token, $type, $title, $body, $data);
            }
            return response()->json([
                'status' => true,
                'error_code' => 200,
                'message' => 'Message sent !',
                'result' => $ret
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error_code' => 105,
                'message' => 'Some error found'
            ]);
        }
    }

    protected function send_image(Request $request)
    {
        $insert_arr = [
            'fk_support_id' => $request->input('support_id'),
            'fk_user_id' => $request->input('user_id'),
            'from' => 'jeeb',
            'type' => 'image'
        ];
        if ($request->hasFile('file')) {
            $path = "/images/support_images/";
            $check = $this->uploadFile($request, 'file', $path);

            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                $returnArr = $this->insertFile($req);
                $insert_arr['message'] = $returnArr->id;
            endif;
        }

        $update = \App\Model\CustomerSupportChat::create($insert_arr);

        if ($update) {
            $ret = [
                'id' => $update->id,
                'created_at' => date('H:i A | d-m-Y', strtotime($update->created_at)),
                'image' => asset('/') . $update->getFile->file_path . $update->getFile->file_name
            ];
            return response()->json([
                'status' => true,
                'error_code' => 200,
                'message' => 'Message sent !',
                'result' => $ret
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error_code' => 105,
                'message' => 'Some error found'
            ]);
        }
    }

    protected function create_customer_support_ticket(Request $request)
    {
        $order = Order::find($request->input('order_id'));

        $customer_support_ticket = $order->getTechnicalSupport;
        if ($customer_support_ticket) {
            return response()->json([
                'status' => false,
                'error_code' => 105,
                'message' => 'Ticket already created'
            ]);
        }

        $NotCollected = StorekeeperProduct::where('fk_order_id', '=', $request->input('order_id'))
            ->where('status', '=', 0)
            ->first();
        if ($NotCollected) {
            return response()->json([
                'status' => false,
                'error_code' => 105,
                'message' => "Can't create ticket now, please wait for all storekeeper to mark product as collected/outofstock"
            ]);
        }

        $res = TechnicalSupport::create([
            'fk_user_id' => $order->fk_user_id,
            'fk_order_id' => $request->input('order_id'),
            'message_type' => 'received',
            'last_message' => 'Hi, Below mentioned products are unfortunately out of stock, please choose appropriate action.',
            'notes' => 'new ticket generated ' . $order->orderId,
            'status' => 1,
        ]);
        if ($res) {
            TechnicalSupportChat::create([
                'ticket_id' => $res->id,
                'chatbox_type' => 1,
                'message_type' => 'received',
                'message' => 'Hi, Below mentioned products are unfortunately out of stock, please choose appropriate action.',
            ]);

            TechnicalSupportProduct::where(['fk_order_id' => $request->input('order_id')])
                ->update(['ticket_id' => $res->id]);

            //sending push notification and in-app notification
            $data = [
                'ticket_id' => $res->id,
                'order_id' => $request->input('order_id'),
                'message_type' => $res->message_type,
                'message' => $res->last_message,
                'created_at' => date('Y-m-d H:i:s', strtotime($res->created_at)),
                'status' => $res->status
            ];
            $this->send_inapp_notification_to_user(
                $order->fk_user_id,
                "Out of stock",
                "",
                "Some of the items of your order $order->orderId are out of stock",
                "",
                3, //for customer support chat
                $data
            );

            $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $order->fk_user_id])->first();
            if ($user_device) {
                $type = 3; //for customer support chat
                $title = "Out of stock";
                $body = "Some of the items of your order $order->orderId are out of stock";

                $this->send_push_notification_to_user($user_device->device_token, $type, $title, $body, $data);
            }

            return response()->json([
                'status' => true,
                'error_code' => 200,
                'message' => 'New ticket created!',
                'result' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error_code' => 105,
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function customer_support(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $tickets = TechnicalSupport::where('last_message', 'like', '%' . $filter . '%')
                ->orderBy('id', 'desc')
                ->sortable(['id', 'desc'])
                ->paginate(20);
        } else {
            $tickets = TechnicalSupport::orderBy('id', 'desc')
                ->sortable(['id', 'desc'])
                ->paginate(20);
        }
        $tickets->appends(['filter' => $filter]);

        return view(
            'admin.support.customer_support_tickets',
            ['tickets' => $tickets, 'filter' => $filter]
        );
    }

    protected function customer_support_detail($id = null)
    {
        $detail = TechnicalSupport::find($id);

        $messages = \App\Model\TechnicalSupportChat::where(['ticket_id' => $id])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.support.customer_support_detail', [
            'detail' => $detail,
            'messages' => $messages
        ]);
    }

    protected function open_close_ticket(Request $request)
    {
        $id = $request->input('id');

        if ($request->input('type') == 'technical_support') {
            $ticket = CustomerSupport::find($id);
        } elseif ($request->input('type') == 'customer_support') {
            $ticket = TechnicalSupport::find($id);
        }
        if ($ticket->status == 1) {
            $status = 0;
        } else {
            $status = 1;
        }
        if ($request->input('type') == 'technical_support') {
            $update = CustomerSupport::find($id)->update(['status' => $status]);
        } elseif ($request->input('type') == 'customer_support') {
            $update = TechnicalSupport::find($id)->update(['status' => $status]);
        }

        if ($update) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Success"
            ]);
        } else {
            return response()->json([
                'error' => false,
                'status_code' => 105,
                'message' => "Some error found"
            ]);
        }
    }
}
