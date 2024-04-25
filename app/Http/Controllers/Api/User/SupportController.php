<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use App\Model\CustomerSupport;
use App\Model\Store;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\OrderDriver;
use App\Model\TechnicalSupport;
use App\Model\TechnicalSupportChat;
use App\Model\TechnicalSupportProduct;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\User;

class SupportController extends CoreApiController
{

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = [
            'send_message', 'get_customer_support_ticket_detail', 'get_technical_support_ticket_detail', 'customer_support',
            'send_response', 'get_all_tickets'
        ];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->user_id = $auth->user_id;
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 301,
                    'message' => "Invalid access token",
                    'result' => (object) []
                ]);
            }
        }

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost()=='localhost' ? 'dev_products' : 'products';
    }

    protected function customer_support(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['subject', 'description']);
            $user = User::find($this->user_id);

            $insert_arr = [
                'fk_user_id' => $this->user_id,
                'email' => $request->input('email'),
                'subject' => $request->input('subject'),
                'description' => $request->input('description'),
                'screenshots' => $request->input('screenshots'),
            ];
            $create = \App\Model\CustomerSupport::create($insert_arr);
            if ($create) {
                $name = $user->name;

                $chat_arr = [
                    'fk_support_id' => $create->id,
                    'fk_user_id' => $this->user_id,
                    'from' => 'user',
                    'message' => $request->input('description'),
                    'type' => 'text'
                ];
                \App\Model\CustomerSupportChat::create($chat_arr);

                if ($request->input('screenshots')) {
                    $screenshot_arr = explode(',', $request->input('screenshots'));
                    if ($screenshot_arr) {
                        foreach ($screenshot_arr as $key => $value) {
                            $more_chat_arr = [
                                'fk_support_id' => $create->id,
                                'fk_user_id' => $this->user_id,
                                'from' => 'user',
                                'message' => $value,
                                'type' => 'image'
                            ];
                            \App\Model\CustomerSupportChat::create($more_chat_arr);
                        }
                    }
                }

                //                \Mail::send('emails.support_email_admin', [
                //                    'name' => $name,
                //                    'email' => $request->input('email'),
                //                    'subject' => $request->input('subject'),
                //                    'description' => $request->input('description')
                //                        ], function ($message) use ($request) {
                //                    $message->from('jeebdeveloper@gmail.com', 'Jeeb');
                //                    $message->to('tahirali2k19@gmail.com');
                //                    $message->subject('Customer Support');
                //                });

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "نشكرك على التواصل" : "Thank you for contacting us!";
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function upload_support_image(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['image']);

            if ($request->hasFile('image')) {
                $path = "/images/support_images/";
                $check = $this->uploadFile($request, 'image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    $returnArr = $this->insertFile($req);
                endif;

                if ($returnArr) {
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang =='ar' ? "تحميل نجاح" : "Upload success";
                    $this->result = [
                        'image_id' => $returnArr->id,
                        'image_url' => asset('images/support_images') . '/' . $returnArr->file_name,
                    ];
                } else {
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_all_tickets(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['type']);

            $ticket_arr = [];
            if ($request->input('type') == 1) { //technical support
                $tickets = \App\Model\CustomerSupport::where(['fk_user_id' => $this->user_id])
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($tickets->count()) {
                    foreach ($tickets as $key => $value) {
                        $ticket_arr[$key] = [
                            'type' => 1,
                            'subject' => $value->subject,
                            'email' => $value->email,
                            'description' => $value->description,
                            'status' => $value->status,
                            'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                            'ticket_id' => $value->id
                        ];

                        if ($value->screenshots != '') {
                            $result_arr = [];

                            $images_arr = explode(',', $value->screenshots);
                            foreach ($images_arr as $key1 => $value1) {
                                $file = \App\Model\File::find($value1);
                                array_push($result_arr, asset('/') . $file->file_path . $file->file_name);
                            }

                            $ticket_arr[$key]['screenshots'] = $result_arr;
                        }
                    }
                }
            } else if ($request->input('type') == 2) { //customer support
                $tickets = \App\Model\TechnicalSupport::where(['fk_user_id' => $this->user_id])
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($tickets->count()) {
                    foreach ($tickets as $key => $value) {
                        $ticket_arr[$key] = [
                            'type' => 2,
                            'message_type' => $value->message_type,
                            'message' => $value->last_message,
                            'notes' => $value->notes,
                            'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                            'order_id' => $value->fk_order_id,
                            'status' => $value->status,
                            'ticket_id' => $value->id
                        ];
                    }
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'tickets' => $ticket_arr
            ];
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_technical_support_ticket_detail(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['ticket_id']);

            $cs = CustomerSupport::find($request->input('ticket_id'));

            $messages = \App\Model\CustomerSupportChat::where(['fk_support_id' => $request->input('ticket_id')])
                ->orderBy('id', 'asc')
                ->get();

            $message_arr = [];
            if ($messages->count()) {
                foreach ($messages as $key => $value) {
                    $message_arr[$key] = [
                        'id' => $value->id,
                        'from' => $value->from,
                        'type' => $value->type,
                        'message' => $value->type == 'text' ? $value->message : '',
                        'image' => $value->type == 'image' ? asset('/') . $value->getFile->file_path . $value->getFile->file_name : '',
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at))
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $last_message = '';
                if ($cs->status==0) {
                    $last_message = $lang =='ar' ? 'تم إغلاق سلسلة الدردشة هذه بواسطة ممثل الدعم الفني ، إذا كنت بحاجة إلى مزيد من المساعدة ، فيرجى بدء موضوع جديد' : 'This chat thread has been closed by Technical Support Representative, if you need further assistence, please start a new thread';
                }
                $this->result = [
                    'status' => $cs->status,
                    'last_message' => $last_message,
                    'messages' => $message_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "لم يتم العثور على رسالة!" : "No message found!";
                $last_message = '';
                if ($cs->status==0) {
                    $last_message = $lang =='ar' ? 'تم إغلاق سلسلة الدردشة هذه بواسطة ممثل الدعم الفني ، إذا كنت بحاجة إلى مزيد من المساعدة ، فيرجى بدء موضوع جديد' : 'This chat thread has been closed by Technical Support Representative, if you need further assistence, please start a new thread';
                }
                $this->result = [
                    'status' => $cs->status,
                    'last_message' => $last_message,
                    'messages' => $message_arr
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function send_message(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['ticket_id', 'type']);

            $chat_arr = [
                'fk_support_id' => $request->input('ticket_id'),
                'fk_user_id' => $this->user_id,
                'from' => 'user',
                'message' => $request->input('message'),
                'type' => $request->input('type'),
            ];

            if ($request->input('type') == 'image' && $request->hasFile('image')) {
                $path = "/images/support_images/";
                $check = $this->uploadFile($request, 'image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    $returnArr = $this->insertFile($req);

                    $chat_arr['message'] = $returnArr->id;
                endif;
            }

            $update = \App\Model\CustomerSupportChat::create($chat_arr);
            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم الارسال!" : "Message sent!";
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_customer_support_ticket_detail(Request $request)
    {
        try {
            $this->required_input($request->input(), ['ticket_id']);

            $lang = $request->header('lang');

            $user = User::find($this->user_id);
            $user->nearest_store = 14;

            $ts = TechnicalSupport::find($request->input('ticket_id'));

            $order = Order::find($ts->fk_order_id);

            $list = TechnicalSupportChat::where(['ticket_id' => $request->input('ticket_id')])
                ->get();
            
            if ($list->count()) {
                foreach ($list as $key => $value) {
                    $required_arr[$key] = [
                        'message_type' => $value->message_type,
                        'message' => $value->message,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'chatbox_type' => $value->chatbox_type,
                        'products' => [],
                        'action' => $value->action ?? '',
                        'products_total' => ''
                    ];

                    if ($value->chatbox_type == 1) {
                        $totalReplacementProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($value->getTechnicalSupport->getOrder->id);
                        if ($totalReplacementProduct) {
                            $required_arr[$key]['products_total'] = $totalReplacementProduct;
                        }
                        $products = OrderProduct::where(['fk_order_id' => $value->getTechnicalSupport->getOrder->id, 'is_out_of_stock' => 1, 'deleted' => 0])
                            ->orderBy('product_name_en', 'asc')
                            ->get();
                            
                        $product_arr1 = [];
                        foreach ($products as $key1 => $value1) {
                            $product_arr1[$key1] = [
                                'product_id' => $value1->fk_product_id,
                                'product_category_id' => $value1->fk_category_id,
                                'product_sub_category_id' => $value1->fk_sub_category_id,
                                'product_category' => $lang == 'ar' ? $value1->category_name_ar : $value1->category_name,
                                'product_brand_id' => $value1->fk_brand_id ?? 0,
                                'product_brand' => $lang == 'ar' ? $value1->brand_name_ar ?? "" : $value1->brand_name ?? "",
                                'product_name' => $lang == 'ar' ? $value1->product_name_ar : $value1->product_name_en,
                                'product_image' => !empty($value1->product_image) ? $value1->product_image_url : '',
                                'product_price' => number_format($value1->single_product_price, 2),
                                'cart_quantity' => $value1->product_quantity,
                                'unit' => $value1->unit,
                                'product_weight' => $value1->product_weight,
                                'product_unit' => $value1->product_unit ?? ''
                            ];
                        }
                        $required_arr[$key]['products'] = $product_arr1;
                    } elseif ($value->chatbox_type == 2) {
                        $products = TechnicalSupportProduct::join($this->products_table, 'technical_support_products.fk_product_id', '=', $this->products_table . '.id')
                            ->select($this->products_table . '.*')
                            ->where(['technical_support_products.ticket_id' => $request->input('ticket_id')])
                            ->where($this->products_table . '.deleted', '=', 0)
                            // ->where($this->products_table . '.store' . $user->nearest_store, '>', 1)
                            ->orderBy($this->products_table . '.product_name_en', 'asc')
                            ->get();
                        // pp($products);
                        $product_arr2 = [];
                        
                        // Get store price
                        $store = Store::where(['id' => $order->fk_store_id, 'status' => 1, 'deleted' => 0])->first();
                        if (!$store) {
                            $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                            throw new Exception($error_message, 105);
                        } else {  
                            $store_no = get_store_no($store->name);
                        }
                        
                        $store_price = 'store' . $store_no . '_price';
                         
                        foreach ($products as $key2 => $value2) {
                            $product_arr2[$key2] = [
                                'product_id' => $value2->id,
                                'product_category_id' => $value2->fk_category_id,
                                'product_sub_category_id' => $value2->fk_sub_category_id,
                                'product_category' => $lang == 'ar' ? $value2->category_name_ar : $value2->category_name,
                                'product_brand_id' => $value2->fk_brand_id ?? 0,
                                'product_brand' => $lang == 'ar' ? $value2->brand_name_ar ?? "" : $value2->brand_name ?? "",
                                'product_name' => $lang == 'ar' ? $value2->product_name_ar : $value2->product_name_en,
                                'product_image' => !empty($value2->product_image) ? $value2->product_image_url : '',
                                'product_price' => number_format($value2->$store_price, 2),
                                'quantity' => $value2->unit,
                                'unit' => $value2->unit,
                                'product_weight' => '',
                                'product_unit' => ''
                            ];
                        }
                        $required_arr[$key]['products'] = $product_arr2;
                    } elseif ($value->chatbox_type == 4) {
                        $products = OrderProduct::where(['fk_order_id' => $ts->fk_order_id, 'is_replaced_product' => 1, 'deleted' => 0])
                            ->orderBy('product_name_en', 'asc')
                            ->get();
                        $product_arr3 = [];
                        foreach ($products as $key3 => $value3) {
                            $product_arr3[$key3] = [
                                'product_id' => $value3->fk_product_id,
                                'product_category_id' => $value3->fk_category_id,
                                'product_sub_category_id' => $value3->fk_sub_category_id,
                                'product_category' => $lang == 'ar' ? $value3->category_name_ar : $value3->category_name,
                                'product_brand_id' => $value3->fk_brand_id ?? 0,
                                'product_brand' => $lang == 'ar' ? $value3->brand_name_ar ?? "" : $value3->brand_name ?? "",
                                'product_name' => $lang == 'ar' ? $value3->product_name_ar : $value3->product_name_en,
                                'product_image' => !empty($value3->product_image) ? $value3->product_image_url : '',
                                'product_price' => number_format($value3->single_product_price, 2),
                                'quantity' => $value3->product_quantity,
                                'unit' => $value3->unit,
                                'product_weight' => $value3->product_weight,
                                'product_unit' => $value3->product_unit ?? ''
                            ];
                        }
                        $required_arr[$key]['products'] = $product_arr3;
                    } else {
                        $required_arr[$key]['products'] = [];
                    }
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = "Success";
                $last_message = '';
                if ($ts->status==0) {
                    $last_message = $lang =='ar' ? 'تم إغلاق سلسلة الدردشة هذه بواسطة ممثل الدعم الفني ، إذا كنت بحاجة إلى مزيد من المساعدة ، فيرجى بدء موضوع جديد' : 'This chat thread has been closed by Technical Support Representative, if you need further assistence, please start a new thread';
                }
                $this->result = [
                    'ticket_id' => $request->input('ticket_id'),
                    'status' => $ts->status,
                    'last_message' => $last_message,
                    'all_messages' => $required_arr
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function send_response(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['ticket_id', 'action']);

            $support = TechnicalSupport::find($request->input('ticket_id'));

            if ($request->input('action') == 'refund' && empty($request->input('refund_type'))) {
                $message_1 = $lang == 'ar' ? "أود استرداد المبلغ المدفوع" : 'I would like to get a refund';
                $message_2 = $lang == 'ar' ? "الرجاء اختيار نوع الاسترداد المفضل لديك" : 'Please choose your preferred refund type';
                $text = TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'message_type' => 'sent',
                    'message' => $message_1,
                    'action' => $request->input('action')
                ]);
                TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'chatbox_type' => 3,
                    'message_type' => 'received',
                    'message' => $message_2
                ]);
                $result = [
                    'message_type' => $text->message_type,
                    'message' => $text->message,
                    'created_at' => date('Y-m-d H:i:s', strtotime($text->created_at)),
                ];
            } elseif ($request->input('action') == 'refund' && $request->input('refund_type') == 'wallet') {
                $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($support->fk_order_id);

                // $res = makeRefund('wallet', $this->user_id, null, $totalOutOfStockProduct, $support->fk_order_id);
                // if (!$res) {
                //     throw new Exception('Some error found in refunding amount to wallet', 105);
                // }
                $message_1 = $lang == 'ar' ? 'أرغب في استرداد أموالي في Wallet' : 'I would like to get a refund in Wallet';
                $message_2 = $lang == 'ar' ? 'نشكرك على سعة صدرك ، ستتم إضافة أموالك إلى محفظتك في غضون 5-10 دقائق.' : 'Thank your for your patience, your funds will be credited in your wallet in the next 5-10 minutes.';
                
                $text = TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'message_type' => 'sent',
                    'message' => $message_1,
                    'action' => $request->input('action')
                ]);
                TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'message_type' => 'received',
                    'message' => $message_2
                ]);

                // Mark the order as invoiced
                Order::find($support->fk_order_id)->update(['status'=>3]);
                $result = [
                    'message_type' => $text->message_type,
                    'message' => $text->message,
                    'created_at' => date('Y-m-d H:i:s', strtotime($text->created_at)),
                ];
                /* --------------------
                // Add the order in the que for driver
                -------------------- */
                OrderDriver::updateOrCreate([
                        'fk_order_id' => $support->fk_order_id],
                    [
                        'fk_order_id' => $support->fk_order_id,
                        'fk_driver_id' => 0,
                        'status' => 0
                    ]
                );
                // Mark the chat as closed
                TechnicalSupport::find($request->input('ticket_id'))->update(['status' => 0]);
            } elseif ($request->input('action') == 'refund' && $request->input('refund_type') == 'card') {
                $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($support->fk_order_id);

                // $res = makeRefund('card', $this->user_id, null, $totalOutOfStockProduct, $support->fk_order_id);
                // if (!$res) {
                //     throw new Exception('Some error found in refunding amount to card', 105);
                // }
                $message_1 = $lang == 'ar' ? 'أرغب في استرداد أموالي في Card' : 'I would like to get a refund in Card';
                $message_2 = $lang == 'ar' ? 'نشكرك على سعة صدرك ، ستتم إضافة أموالك إلى محفظتك في غضون 5-10 دقائق.' : 'Thank your for your patience, your funds will be credited in your wallet in the next 5-10 minutes.';
                
                $text = TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'message_type' => 'sent',
                    'message' => $message_1,
                    'action' => $request->input('action')
                ]);
                TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'message_type' => 'received',
                    'message' => $message_2
                ]);
                $result = [
                    'message_type' => $text->message_type,
                    'message' => $text->message,
                    'created_at' => date('Y-m-d H:i:s', strtotime($text->created_at)),
                ];
                
                // Mark the order as invoiced
                Order::find($support->fk_order_id)->update(['status'=>3]);
                $result = [
                    'message_type' => $text->message_type,
                    'message' => $text->message,
                    'created_at' => date('Y-m-d H:i:s', strtotime($text->created_at)),
                ];
                /* --------------------
                // Add the order in the que for driver
                -------------------- */
                OrderDriver::updateOrCreate([
                        'fk_order_id' => $support->fk_order_id],
                    [
                        'fk_order_id' => $support->fk_order_id,
                        'fk_driver_id' => 0,
                        'status' => 0
                    ]
                );
                // Mark the chat as closed
                TechnicalSupport::find($request->input('ticket_id'))->update(['status' => 0]);
            } elseif ($request->input('action') == 'replace' && empty($request->input('refund_type'))) {
                $message_1 = $lang == 'ar' ? 'أود الحصول على بديل' : 'I would like to get replacement';
                $message_2 = $lang == 'ar' ? 'هناك خيارات قليلة لك بدلاً من المنتجات غير المتوفرة في المخزون ، يرجى اختيار الإجراء المناسب.' : 'There are few options for you in place of out of stock products, please choose appropriate action.';
                
                $text = TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'message_type' => 'sent',
                    'message' => $message_1,
                    'action' => $request->input('action')
                ]);
                TechnicalSupportChat::create([
                    'ticket_id' => $request->input('ticket_id'),
                    'chatbox_type' => 2,
                    'message_type' => 'received',
                    'message' => $message_2
                ]);

                $result = [
                    'message_type' => $text->message_type,
                    'message' => $text->message,
                    'created_at' => date('Y-m-d H:i:s', strtotime($text->created_at)),
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "تم ارسال الرد" : "Response sent!";
            $this->result = $result;
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }
}
