<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\ScratchCardUser;
use App\Model\Coupon;
use App\Model\CouponUses;
use App\Model\Order;
use App\Model\UserCart;
use App\Model\OrderProduct;
use App\Model\OrderStatus;
use App\Model\OrderReview;
use App\Model\UserAddress;
use App\Model\OrderAddress;
use App\Model\OrderDriver;
use App\Model\OrderDeliverySlot;
use App\Model\OrderPayment;
use App\Model\DeliverySlot;
use App\Model\User;
use App\Model\Store;
use App\Model\UserDeviceDetail;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\UserBuyItForMeCart;
use App\Model\UserBuyItForMeRequest;

use Illuminate\Support\Facades\Http;

class OrderBpTapController extends CoreApiController
{

    use \App\Http\Traits\PushNotification;
    use \App\Http\Traits\OrderProcessing;
    use \App\Http\Traits\TapPayment;

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];
    protected $order_status = [
        '0' => 'Processing',
        '1' => 'Order confirmed',
        '2' => 'Order assigned',
        '3' => 'Order invoiced',
        '4' => 'Cancelled',
        '6' => 'Out for delivery',
        '7' => 'Order delivered'
    ];
    protected $order_status_ar = [
        '0' => 'يعالج',
        '1' => 'تم تاكيد الطلب',
        '2' => 'تم تعيين طلبك',
        '3' => 'تم تجهيز طلبك',
        '4' => 'تم الغاء طلبك',
        '6' => 'طلبك على الطريق',
        '7' => 'تم توصيل طلبك'
    ];
    protected $order_preferences = [
        '0' => "Contactless Delivery",
        '1' => "Don't Ring/Knock",
        '2' => "Beware of Pets",
        '3' => "Leave at Door",
    ];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = [
            'get_scratch_cards', 'scratch_card_scratched', 'get_coupons', 'get_payment_cards', 'add_payment_card',
            'apply_coupon', 'get_delivery_charge', 'place_order', 'my_orders', 'my_orders_opmtized',
            'rate_order', 'rate_product', 'reorder', 'confirm_order', 'cancel_order',
            'order_detail', 'make_order', 'getOrderRefId', 
            'make_order_pay', 'make_order_pay_by_token', 'make_order_pay_by_card_id', 'get_order_pay_status',
            'get_subtotal_forgot_something_order', 'make_forgot_something_order', 
            'make_forgot_something_order_pay', 'make_forgot_something_order_pay_by_card_id', 'get_forgot_something_order_pay_status',
            'get_subtotal_replacement_order', 'make_replacement_order', 'filter_orders', 'filter_order_products'
        ];

        //setting user id which will be accessable for all functions
        $this->user_id = 0;
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

        $this->loadApi = new \App\Model\LoadApi();

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function get_scratch_cards(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $scratch_cards = ScratchCardUser::where('status', '!=', 4)
                ->where('fk_user_id',$this->user_id)
                ->where('deleted',0)
                ->orderBy('status', 'asc')
                ->orderBy('fk_order_id', 'desc')
                ->get();
            $redeemed_value = 0;

            if ($scratch_cards->count()) {
                foreach ($scratch_cards as $key => $value) {
                    // Scratch cards redeemed value
                    if($value->status==3) {
                        $redeemed_value += (float) $value->redeem_amount;
                    }
                    // Scratch card type
                    switch ($value->scratch_card_type) {
                        case 0:
                            $scratch_card_type_name = 'Onspot Reward';
                            break;
                        case 1:
                            $scratch_card_type_name = 'Coupon';
                            break;
                        default:
                            $scratch_card_type_name = 'Unknown';
                            break;
                    }
                    // Expiry
                    $expired = false;
                    if($value->status<3) {
                        $expiry_date = $value->expiry_date;
                        if($expiry_date != NULL) {
                            if(strtotime($expiry_date.' 23:59:59') < time()) {
                                $expired = true;
                            }
                        }
                    }
                    // Scratch card image
                    if ($lang=='ar') {
                        $image = $value->getScratchCardImageAr ? asset('/') . $value->getScratchCardImageAr->file_path . $value->getScratchCardImageAr->file_name : '';
                    } else {
                        $image = $value->getScratchCardImage ? asset('/') . $value->getScratchCardImage->file_path . $value->getScratchCardImage->file_name : '';
                    }
                    // Scratch card array
                    $scratch_card_arr[$key] = [
                        'id' => $value->id,
                        'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                        'description' => $lang == 'ar' ? $value->description_ar : $value->description_en,
                        'image' => $image,
                        'status' => $value->status,
                        'expired' => $expired,
                        'expiry_date' => $value->expiry_date,
                        'order_id' => $value->fk_order_id,
                        'order_no' => $this->get_order_number($value->fk_order_id),
                        'scratch_card_type' => $value->scratch_card_type,
                        'scratch_card_type_name' => $scratch_card_type_name,
                        'redeem_amount' => (float) $value->redeem_amount,
                        'coupon_code' => $value->coupon_code
                    ];
                    // Custom description
                    if ($value->status==2) {
                        $scratch_card_arr[$key]['description'] = $lang == 'ar' ? $scratch_card_arr[$key]['description']." تهانينا! " : "Congratulations! ".$scratch_card_arr[$key]['description'];
                    } elseif ($value->status==3) {
                        if ($value->scratch_card_type==0) {
                            $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "تهانينا! تم استرداد جائزتك الى محفظتك" : "Congratulations! your reward has been already transferred to your wallet.";
                        } elseif ($value->scratch_card_type==1) {
                            $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "تهانينا! لقد تم بالفعل استرداد الجائزة من الكوبون" : "Congratulations! you have already redeemed rewards from this coupon.";
                        } else {
                            $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "تهانينا! بطاقة الخدش تم استردادها بالفعل" : "Congratulations! your scratch card is already redeemed.";
                        }
                    } elseif ($expired) {
                        $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "عذراً، بطاقة الخدش انتهت صلاحيتها" : "Sorry, your scratch card is expired!";
                    }
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Scratch Card List";
                $this->result = [
                    'redeemed_value' => number_format((float) $redeemed_value, 2, '.', ''),
                    'scratch_cards' => $scratch_card_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد هدايا الآن،اطلب من جيب و احصل على الجوائز" : "No rewards are available right now, place an order and get rewards!";
                $this->result = [
                    'scratch_cards' => []
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

    protected function scratch_card_scratched(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $tracking_id = add_tracking_data($this->user_id, 'scratch_card_scratched', $request, '');
            $this->required_input($request->input(), ['scratch_card_id']);

            $scratched = false;
            $redeemed = false;
            $scratch_card = ScratchCardUser::where('id', '=', $request->input('scratch_card_id'))
                ->where('fk_user_id',$this->user_id)
                ->where('deleted',0)
                ->first();

            if ($scratch_card) {
                $message = "Scratch card scratching failed";
                // Check status
                if ($scratch_card->status==0) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Scratch card is inactive!");
                    }
                    throw new Exception($lang == 'ar' ? "هذه البطاقة غير مفعلة" : "Scratch card is inactive!", 105);
                } elseif ($scratch_card->status==2) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Scratch card is already scratched!");
                    }
                    throw new Exception($lang == 'ar' ? "البطاقة تم تفعيلها" : "Scratch card is already scratched!", 105);
                } elseif ($scratch_card->status==3) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Scratch card is already redeemed!");
                    }
                    throw new Exception($lang == 'ar' ? "البطاقة مستخدمة بالفعل" : "Scratch card is already redeemed!", 105);
                } elseif ($scratch_card->status==4) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Scratch card is cancelled!");
                    }
                    throw new Exception($lang == 'ar' ? "تم حذف البطاقة" : "Scratch card is cancelled!", 105);
                } elseif ($scratch_card->status!=1) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Scratch card not found!");
                    }
                    throw new Exception($lang == 'ar' ? "رقم البطاقة غير موجودة" : "Scratch card not found!", 105);
                }
                // Check expiry
                $expired = false;
                $expiry_date = $scratch_card->expiry_date;
                if($expiry_date != NULL) {
                    if(strtotime($expiry_date.' 23:59:59') < time()) {
                        $expired = true;
                    }
                }
                switch ($scratch_card->scratch_card_type) {
                    case 0:
                        $scratch_card_type_name = 'Onspot Reward';
                        break;
                    case 1:
                        $scratch_card_type_name = 'Coupon';
                        break;
                    default:
                        $scratch_card_type_name = 'Unknown';
                        break;
                }
                // Process Scratch card
                if (!$expired) {
                    switch ($scratch_card->scratch_card_type) {
                        case 0:
                            // Onspot Reward
                            $redeem_amount = $scratch_card->discount;
                            $orderId = 0;
                            $order = false;
                            if ($scratch_card->fk_order_id!=0) {
                                $order = Order::find($scratch_card->fk_order_id);
                                if(!$order) {
                                    if (isset($tracking_id) && $tracking_id) {
                                        update_tracking_response($tracking_id,"Order doesn't exist");
                                    }
                                    throw new Exception("Order doesn't exist", 105);
                                }
                                if ($order->status!=7) {
                                    if (isset($tracking_id) && $tracking_id) {
                                        update_tracking_response($tracking_id,"Order is not delivered yet");
                                    }
                                    throw new Exception("Order is not delivered yet", 105);
                                }
                            }
                            if ($scratch_card->type==1 && $order) {
                                $order_amount = $order->grand_total;
                                $redeem_amount = $order_amount*($scratch_card->discount/100);
                                $orderId = $order->orderId;
                            }
                            $redeemed = $this->make_refund_in_order('wallet', $this->user_id, 0, $redeem_amount, $orderId, 5);
                            
                            $scratched=ScratchCardUser::find($scratch_card->id)->update([
                                'scratched_at'=>date('Y-m-d H:i:s'),
                                'status'=>3,
                                'redeem_amount'=>$redeem_amount,
                                'redeemed_at'=>date('Y-m-d H:i:s')
                            ]);
                            $message = "Scratch card is redeemed succesfully";
                            break;
                        case 1:
                            // Create new coupon for the user
                            $coupon_arr = $scratch_card->toArray();
                            $coupon_arr['id'] = NULL;
                            $coupon_arr['offer_type'] = 1;
                            $coupon_arr['status'] = 1;
                            $coupon_arr['coupon_image'] = $scratch_card->image;
                            $coupon_arr['coupon_image_ar'] = $scratch_card->image_ar;
                            Coupon::create($coupon_arr);

                            $scratched=ScratchCardUser::find($scratch_card->id)->update([
                                'scratched_at'=>date('Y-m-d H:i:s'),
                                'status'=>2
                            ]);
                            $message = "Scratch card is scracthed succesfully";
                            break;
                        default:
                            
                            break;
                    }
                } else {
                    throw new Exception($lang == 'ar' ? "عذراً، بطاقة الخدش انتهت صلاحيتها" : "Sorry, your scratch card is expired!", 105);
                }
                $scratch_card = $scratch_card->refresh();
                $scratch_card_arr = [
                    'id' => $scratch_card->id,
                    'title' => $lang == 'ar' ? $scratch_card->title_ar : $scratch_card->title_en,
                    'description' => $lang == 'ar' ? $scratch_card->description_ar : $scratch_card->description_en,
                    'image' => $scratch_card->getScratchCardImage ? asset('/') . $scratch_card->getScratchCardImage->file_path . $scratch_card->getScratchCardImage->file_name : '',
                    'status' => $scratch_card->status,
                    'expired' => $expired,
                    'expiry_date' => $scratch_card->expiry_date,
                    'order_id' => $scratch_card->fk_order_id,
                    'order_no' => $this->get_order_number($scratch_card->fk_order_id),
                    'scratch_card_type' => $scratch_card->scratch_card_type,
                    'scratch_card_type_name' => $scratch_card_type_name,
                    'coupon_code' => $scratch_card->coupon_code
                ];
                $this->error = false;
                $this->status_code = 200;
                $this->message = $message;
                $this->result = [
                    'scratched' => $scratched,
                    'redeemed' => $redeemed,
                    'scratch_card' => $scratch_card_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "رقم البطاقة غير موجودة" : "Scratch card not found!";
                $this->result = [
                    'scratch_cards' => []
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
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_coupons(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $delivery_time = $request->input('delivery_time') ?? 0;
            $user_id = (isset($this->user_id) && $this->user_id!='') ? $this->user_id : 0;
            $coupon_arr=[];
            $scratch_card_arr=[];
            $product_arr=[];
            
            // ------------
            // Get all available coupons
            // ------------
            $coupons = Coupon::where('status', '=', 1)->where('expiry_date', '>', \Carbon\Carbon::now()->format('Y-m-d'));
            // Check for user specific and general
            if ($user_id!=0) {
                $coupons = $coupons->whereIn('coupons.fk_user_id',[0,$user_id]);
            } else {
                $coupons = $coupons->where('fk_user_id',0);
            }
            // Check for instance or plus or general
            if ($delivery_time!=0) {
                $coupons = $coupons->whereIn('delivery_time',[0,$delivery_time]);
            } else {
                $coupons = $coupons->where('delivery_time',0);
            }
            $coupons = $coupons->orderBy('expiry_date', 'asc')->get();
            if ($coupons->count()) {
                foreach ($coupons as $value) {
                    $coupon_used = 0;
                    // Check for user coupon usage
                    if ($user_id!=0) {
                        $coupon_used = CouponUses::where(['fk_user_id'=>$this->user_id,'fk_coupon_id'=>$value->id])->count();
                    }
                    if ($coupon_used<$value->uses_limit) {
                        $coupon_arr[] = [
                            'id' => $value->id,
                            'type' => $value->type,
                            'min_amount' => (float) $value->min_amount,
                            'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                            'description' => $lang == 'ar' ? $value->description_ar : $value->description_en,
                            'coupon_code' => $value->coupon_code,
                            'coupon_image' => $value->getCouponImage ? asset('/') . $value->getCouponImage->file_path . $value->getCouponImage->file_name : '',
                            'expiry_date' => $value->expiry_date,
                            'uses_limit' => $value->uses_limit,
                            'coupon_used' => $coupon_used
                        ];
                    }
                }
            } 
            
            // ------------
            // Get all unscratched scratch cards
            // ------------
            $is_scratch_card_enabled = $this->is_scratch_card_enabled($this->user_id);
            if ($is_scratch_card_enabled) {
                $scratch_cards = ScratchCardUser::where('fk_user_id',$this->user_id)
                    ->where('deleted',0)
                    ->whereIn('status',[0,1])
                    ->where('expiry_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))
                    ->orderBy('status', 'asc')
                    ->orderBy('fk_order_id', 'desc')
                    ->get();
    
                if ($scratch_cards->count()) {
                    foreach ($scratch_cards as $key => $value) {
                        // Scratch card type
                        switch ($value->scratch_card_type) {
                            case 0:
                                $scratch_card_type_name = 'Onspot Reward';
                                break;
                            case 1:
                                $scratch_card_type_name = 'Coupon';
                                break;
                            default:
                                $scratch_card_type_name = 'Unknown';
                                break;
                        }
                        // Expiry
                        $expired = false;
                        if($value->status<3) {
                            $expiry_date = $value->expiry_date;
                            if($expiry_date != NULL) {
                                if(strtotime($expiry_date.' 23:59:59') < time()) {
                                    $expired = true;
                                }
                            }
                        }
                        // Scratch card image
                        if ($lang=='ar') {
                            $image = $value->getScratchCardImageAr ? asset('/') . $value->getScratchCardImageAr->file_path . $value->getScratchCardImageAr->file_name : '';
                        } else {
                            $image = $value->getScratchCardImage ? asset('/') . $value->getScratchCardImage->file_path . $value->getScratchCardImage->file_name : '';
                        }
                        // Scratch card array
                        $scratch_card_arr[$key] = [
                            'id' => $value->id,
                            'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                            'description' => $lang == 'ar' ? $value->description_ar : $value->description_en,
                            'image' => $image,
                            'status' => $value->status,
                            'expired' => $expired,
                            'expiry_date' => $value->expiry_date,
                            'order_id' => $value->fk_order_id,
                            'order_no' => $this->get_order_number($value->fk_order_id),
                            'scratch_card_type' => $value->scratch_card_type,
                            'scratch_card_type_name' => $scratch_card_type_name,
                            'redeem_amount' => (float) $value->redeem_amount,
                            'coupon_code' => $value->coupon_code
                        ];
                        // Custom description
                        if ($value->status==2) {
                            $scratch_card_arr[$key]['description'] = $lang == 'ar' ? $scratch_card_arr[$key]['description']." تهانينا! " : "Congratulations! ".$scratch_card_arr[$key]['description'];
                        } elseif ($value->status==3) {
                            if ($value->scratch_card_type==0) {
                                $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "تهانينا! تم استرداد جائزتك الى محفظتك" : "Congratulations! your reward has been already transferred to your wallet.";
                            } elseif ($value->scratch_card_type==1) {
                                $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "تهانينا! لقد تم بالفعل استرداد الجائزة من الكوبون" : "Congratulations! you have already redeemed rewards from this coupon.";
                            } else {
                                $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "تهانينا! بطاقة الخدش تم استردادها بالفعل" : "Congratulations! your scratch card is already redeemed.";
                            }
                        } elseif ($expired) {
                            $scratch_card_arr[$key]['description'] = $lang == 'ar' ? "عذراً، بطاقة الخدش انتهت صلاحيتها" : "Sorry, your scratch card is expired!";
                        }
                    }
                }
            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = "Coupon List";
            $this->result = [
                'coupons' => $coupon_arr,
                'scratch_cards' => $scratch_card_arr,
                'products' => $product_arr
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

    protected function apply_coupon(Request $request)
    {
        try {
            $lang = $request->header('lang');
            
            $tracking_id = add_tracking_data($this->user_id, 'apply_coupon', $request, '');
            $this->required_input($request->input(), ['coupon_code']);

            $total_amount = $request->input('total_amount') ? (float) $request->input('total_amount') : 0;
            $delivery_time = $request->input('delivery_time') ?? 0;
            $user_id = (isset($this->user_id) && $this->user_id!='') ? $this->user_id : 0;

            $coupon = Coupon::where(['coupon_code' => $request->input('coupon_code'), 'status' => 1]);
            // Check for user specific and general
            if ($user_id!=0) {
                $coupon = $coupon->whereIn('fk_user_id',[0,$user_id]);
            } else {
                $coupon = $coupon->where('fk_user_id',0);
            }
            // Check for instance or plus or general
            if ($delivery_time!=0) {
                $coupon = $coupon->whereIn('delivery_time',[0,$delivery_time]);
            } else {
                $coupon = $coupon->where('delivery_time',0);
            }
            $coupon = $coupon->first();

            // Check coupon exists or not
            if (!$coupon) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Coupon doesn't exist");
                }
                throw new Exception($lang == 'ar' ? "الكويون غير صالح" : "Coupon doesn't exist", 105);
            }
            // Check the expiry date
            if ($coupon->expiry_date<date("Y-m-d")) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Coupon is expired');
                }
                throw new Exception($lang == 'ar' ? "الكوبون منتهي الصلاحية" : "Coupon is expired", 105);
            }
            // Check coupon usage exceeded
            $coupon_uses = CouponUses::where(['fk_user_id' => $this->user_id, 'fk_coupon_id' => $coupon->id])->count();
            if ($coupon_uses && $coupon_uses >= $coupon->uses_limit) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Coupon Usage exceeded');
                }
                throw new Exception($lang == 'ar' ? "تم تعدي المحاولات المسموحة" : "Coupon Usage exceeded", 105);
            }
            // Check coupon minimum amount
            if ($coupon->type == 1) {
                if ($total_amount >= $coupon->min_amount) {
                    $discount = (string) round(($total_amount * $coupon->discount) / 100);
                    $amount_after_discount = $total_amount - (float) $discount;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'This coupon is applicable for purchase amount of '.$coupon->min_amount.' QAR');
                    }
                    throw new Exception($lang == 'ar' ? "يمكن استخدام كود الخصم عند الشراء بقيمة $coupon->min_amount QAR قطري" : "This coupon is applicable for purchase amount of $coupon->min_amount QAR", 105);
                }
            } elseif ($coupon->type == 2) {
                if ($total_amount >= $coupon->min_amount) {
                    $discount = (string) $coupon->discount;
                    $amount_after_discount = $total_amount - (float) $discount;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'This coupon is applicable for purchase amount of '.$coupon->min_amount.' QAR');
                    }
                    throw new Exception($lang == 'ar' ? "يمكن استخدام كود الخصم عند الشراء بقيمة $coupon->min_amount QAR قطري" : "This coupon is applicable for purchase amount of $coupon->min_amount QAR", 105);
                }
            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم اضافة الكوبون" : "Coupon applied";
            $this->result = [
                'total_amount' => (float) $total_amount,
                'total_amount_after_discount' => (float) $amount_after_discount,
                'coupon_discount' => (float) $discount,
                'coupon_id' => $coupon->id,
                'type' => $coupon->type,
                'min_amount' => (float) $coupon->min_amount,
                'discount' => (float) $coupon->discount
            ];
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function delivery_slots(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $isTodaySlotAvailable = DeliverySlot::where('date', '=', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('from', '>=', \Carbon\Carbon::now()->subMinutes(60)->format('H:i:s'))
                ->count();

            if ($isTodaySlotAvailable == 0) {
                $slot_dates = DeliverySlot::where('date', '<=', \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'))
                    ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
                    ->groupBy('date')
                    ->orderBy('id', 'asc')
                    ->get();
            } else {
                $slot_dates = DeliverySlot::where('date', '<=', \Carbon\Carbon::now()->addDays(2)->format('Y-m-d'))
                    ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
                    ->groupBy('date')
                    ->orderBy('id', 'asc')
                    ->get();
            }

            // pp($slot_dates);die;

            $slot_arr = [];
            if ($slot_dates->count()) {
                foreach ($slot_dates as $key => $value) {
                    $slot_arr[$key]['date'] = $value->date;

                    // $slots = DeliverySlot::where(['date' => $value->date])
                    //         ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'))
                    //         ->orderBy('from', 'asc')
                    //         ->get();
                    $slots = DeliverySlot::where(['date' => $value->date])
                        ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
                        ->orderBy('from', 'asc')
                        ->get();

                    $slot = [];
                    if ($slots->count()) {
                        foreach ($slots as $key1 => $value1) {
                            $noOfOrdersInThisSlot = \App\Model\OrderDeliverySlot::where('delivery_date', '=', $value->date)
                                ->where('delivery_slot', '=', date('H:i', strtotime($value1->from)) . '-' . date('H:i', strtotime($value1->to)))
                                ->orderBy('id', 'desc')
                                ->count();

                            $slot_date_time = $value->date . ' ' . date('H:i:s', strtotime($value1->from));
                            // if ($slot_date_time < \Carbon\Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s')) {
                            if ($slot_date_time < \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s')) {
                                $is_full = 0;
                            } else {
                                $is_full = 0;
                            }

                            $slot[$key1]['is_full'] = $is_full;
                            $slot[$key1]['range'] = date('H:i', strtotime($value1->from)) . '-' . date('H:i', strtotime($value1->to));
                        }
                    }
                    $slot_arr[$key]['slot'] = $slot;
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'slots' => $slot_arr
                ];
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

    protected function getOrderRefId(Request $request)
    {
        try {
            $lang = $request->header('lang');

            // Check and confirm order is not invoiced yet
            if ($request->id && $request->id!="") {
                $parent_order = Order::find($request->input('id'));
                if (!$parent_order) {
                    throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
                }
                elseif ($parent_order->status>=3) {
                    throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
                }
            }

            $refId = time().$this->user_id;

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['refId' => $refId];
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

    protected function make_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['sub_total', 'delivery_charge', 'delivery_date', 'payment_type', 'address_id']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_order_bp_tap', $request, '');
            
            // Find user and assign Tap reference ID if not existing
            $user = User::find($this->user_id);
            // If user not found, send session expired message
            if(!$user){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Your session expired. Please login again..');
                }
                $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                throw new Exception($error_message, 105);
            }
            // Setup Tap Payment ID
            $tap_id = $user->tap_id;
            if ($tap_id=="") {
                $tap_id = $this->create_customer($user);
                User::find($user->id)->update([
                    'tap_id' => $tap_id
                ]);
            }

            // Test numbers array
            $test_numbers = explode(',', env("TEST_NUMBERS"));

            // Check user cart exist and paythem product
            $cart_items = UserCart::where('fk_user_id', $this->user_id)->get();
            $types_in_cart = [];
            foreach ($cart_items as $item) {
                $product = BaseProduct::find($item->fk_product_id);
                if ($product) {
                    $product_type = $product->product_type;
                    if (!in_array($product_type, $types_in_cart)) {
                        $types_in_cart[] = $product_type;
                    }
                }
            }
            $is_paythem_only = (count($types_in_cart) === 1 && isset($types_in_cart[0]) && $types_in_cart[0] == 'paythem') ? 1 : 0;
            
            $payment_type = $request->input('payment_type');
            $delivery_time = $request->input('delivery_time');
            $later_time = $request->input('later_time');
            $delivery_date = $request->input('delivery_date');
            $delivery_charge = $request->input('delivery_charge');

            if ($is_paythem_only) {
                // No minimum and only online payment allowed
                // if($payment_type != 'online'){
                //     throw new Exception($lang == 'ar' ? "الدفع عند الاستلام لا ينطبق على أي من عناصر بطاقات الهدايا في سلة التسوق الخاصة بك." : "Cash on Delivery is not applicable for any Gift Card items in your cart.", 105);
                // }
                // Instant delivery only
                $delivery_time = 1;
                $start_time = date("H:i", strtotime("now"));
                $end_time = date("H:i", strtotime("now") + 1800);
                $later_time = $start_time.'-'.$end_time;
                // Delivery charge is 0
                $delivery_charge = 0;
            }else{
                // Check minimum purchase amount for test numbers 1QAR
                $minimum_purchase_amount = get_minimum_purchase_amount($user->ivp,$user->mobile,$user->id);
                if ($request->input('sub_total') < $minimum_purchase_amount) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Minimum Order is $minimum_purchase_amount QAR");
                    }
                    throw new Exception($lang == 'ar' ? "الطلب الأدنى $minimum_purchase_amount ريال قطري" : "Minimum Order is $minimum_purchase_amount QAR", 105);
                }
                
                // ------------------------------------------------------------------------------------------------------
                // Check store closed or not, if closed reshedule it to next day morning or later in the morning
                // ------------------------------------------------------------------------------------------------------
                $store_open_time = env("STORE_OPEN_TIME");
                $store_close_time = env("STORE_CLOSE_TIME");
                if ($delivery_time == 1) {
                    if(strtotime($store_open_time) > time()) {
                        $delivery_time = 2;
                        $later_time = '09:00-10:00';
                    } elseif (strtotime($store_close_time) < time()) {
                        $delivery_time = 2;
                        $later_time = '09:00-10:00';
                        $datetime = new \DateTime('tomorrow');
                        $datetime = $datetime->format('Y-m-d');
                        $delivery_date = $datetime;
                    } else {
                        $start_time = date("H:i", strtotime("now") + $user->expected_eta);
                        $end_time = date("H:i", strtotime("now") + $user->expected_eta + 1800);
                        $later_time = $start_time.'-'.$end_time;
                    }
                }
                // Format the later time
                if ($delivery_time == 2) {
                    // No timeslot sent from frontend
                    if (!$later_time) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Please select the time slot');
                        }
                        $error_message = $lang == 'ar' ? "الرجاء تحديد الفترة الزمنية" : "Please select the time slot";
                        throw new Exception($error_message, 105);
                    }
                    $later_time_blocked_slots = $request->input('later_time_blocked_slots') ?? 0;
                    $time_slot_validity = $this->validateOrderTimeslot($later_time, $delivery_date, $later_time_blocked_slots);
                    // Timeslot is not valid
                    if (!isset($time_slot_validity[0]) || !$time_slot_validity[0]) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Time slot expired, choose different time slot');
                        }
                        $error_message = $lang == 'ar' ? "الرجاء تحديد الفترة الزمنية" : "Time slot expired, choose different time slot";
                        throw new Exception($error_message, 105);
                    }
                    // Timeslot is expired
                    if (!isset($time_slot_validity[1]) || !$time_slot_validity[1]) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Time slot expired, choose different time slot');
                        }
                        $error_message = $lang == 'ar' ? "انتهت الفترة، اختر وقت ثاني" : "Time slot expired, choose different time slot";
                        throw new Exception($error_message, 105);
                    }
                    // Format the timeslot
                    $later_time = isset($time_slot_validity[2]) ? $time_slot_validity[2] : $later_time;
                }

            }

            // add/update the device detail 
            if ($request->input('device_token')!= '') {
                $device_detail_arr = [
                    'fk_user_id' => $this->user_id,
                    'device_type' => $request->input('device_type') ?? 0, //1:iOS,2:android
                    'app_version' => $request->input('app_version') ?? 'not_detected',
                    'device_token' => $request->input('device_token') ?? '123456789',
                ];
                if (UserDeviceDetail::where(['fk_user_id' => $this->user_id])->first()) {
                    UserDeviceDetail::where(['fk_user_id' => $this->user_id])->update($device_detail_arr);
                } else {
                    UserDeviceDetail::create($device_detail_arr);
                }
            }

            // if ($this->user_id == 2) {
            //     throw new Exception("Can't place new order, You are blocked by admin", 105);
            // }

            // Buy for me feature
            // Check user cart is empty or not
            if($request->input('buy_it_for_me_request_id')){
                
                $exist = UserBuyItForMeCart::where(['fk_request_id' => $request->input('buy_it_for_me_request_id')])->get();
                if (!$exist->count()) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Cart is empty');
                    }
                    throw new Exception($lang == 'ar' ? "سلتك فاضية" : "Cart is empty", 105);
                }
            }else{

                $exist = UserCart::where(['fk_user_id' => $this->user_id])->get();
                if (!$exist->count()) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Cart is empty');
                    }
                    throw new Exception($lang == 'ar' ? "سلتك فاضية" : "Cart is empty", 105);
                }
            }

            $total_amount = $request->input('sub_total') + $delivery_charge;
            $payment_type = $request->input('payment_type');

            /* Order payment */
            if ($request->input('use_wallet_balance') == 1) {
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                    $status = 'success';

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                    if ($payment_type=='online') {
                        // Process cart payment
                        $status = "pending";
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;
    
                        $insert_arr = [
                            'amount' => $cart_payment_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => 'success'
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                    
                } else {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $status = "pending";

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => 'success'
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                }
            } else {
                if ($payment_type=='online') {
                    // Process cart payment
                    $status = "pending";

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $request->input('paymentResponse')
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } else {
                    // Considering all others are COD
                    $status = 'success';

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                }
            }
            
            /* Order creation */
            if ($paid) {
                
                // Buy for me feature
                if($request->input('buy_it_for_me_request_id')){
                    $bought_for = UserBuyItForMeRequest::find($request->input('buy_it_for_me_request_id'));
                    $insert_arr = [
                        'fk_user_id' => $bought_for->from_user_id,
                        'bought_by' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'delivery_charge' => $delivery_charge,
                        'payment_type' => $request->input('payment_type'), //cod or online
                        'order_type' => 1,
                    ];
                }else{
                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'delivery_charge' => $delivery_charge,
                        'payment_type' => $request->input('payment_type'), //cod or online
                    ];
                }

                // coupon applied
                if ($request->input('coupon_id') != '') {
                    $this->required_input($request->input(), ['coupon_discount']);

                    $insert_arr['fk_coupon_id'] = $request->input('coupon_id');
                    $insert_arr['coupon_discount'] = $request->input('coupon_discount');

                    $total_amount = $total_amount - $request->input('coupon_discount');
                }

                // Setup the amount from card / COD / wallet
                if ($request->input('use_wallet_balance') == 1) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                    if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                        $insert_arr['amount_from_wallet'] = $total_amount;
                        $insert_arr['amount_from_card'] = 0;
                        $amount_from_wallet = $total_amount;
                        // Deduct wallet
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                        $UserWalletPayment = \App\Model\UserWalletPayment::create([
                            'fk_user_id' => $this->user_id,
                            'amount' => $amount_from_wallet,
                            'status' => $status,
                            'wallet_type' => 2,
                            'transaction_type' => 'debit'
                        ]);
                    } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                        $insert_arr['amount_from_wallet'] = $user_wallet->total_points;
                        $insert_arr['amount_from_card'] = $total_amount - $user_wallet->total_points;
                        $amount_from_wallet = $user_wallet->total_points;
                        // Deduct wallet only for COD
                        if ($payment_type=='cod') {
                            \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                                'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                            ]);
                            $UserWalletPayment = \App\Model\UserWalletPayment::create([
                                'fk_user_id' => $this->user_id,
                                'amount' => $amount_from_wallet,
                                'status' => $status,
                                'wallet_type' => 2,
                                'transaction_type' => 'debit'
                            ]);
                        }
                    } else {
                        $insert_arr['amount_from_wallet'] = 0;
                        $insert_arr['amount_from_card'] = $total_amount;

                        $amount_from_wallet = 0;
                    }

                } else {
                    $insert_arr['amount_from_wallet'] = 0;
                    $insert_arr['amount_from_card'] = $total_amount;
                }

                $insert_arr['amount_from_card'] = $insert_arr['amount_from_card'];
                $insert_arr['total_amount'] = $total_amount;
                $insert_arr['grand_total'] = $total_amount;
                $insert_arr['status'] = 0;
                $insert_arr['change_for'] = $request->input('payment_type') == "cod" ? $request->input('change_for') : '';

                $insert_arr['fk_store_id'] = $user->nearest_store;
                $insert_arr['buy_it_for_me_request_id'] = $request->input('buy_it_for_me_request_id');

                $insert_arr['is_paythem_only'] = $is_paythem_only;
                $insert_arr['products_type'] = implode(',',$types_in_cart);

                // Setup test orders
                if (in_array($user->mobile, $test_numbers)) {
                    $insert_arr['test_order'] = 1;
                } else {
                    $insert_arr['test_order'] = 0;
                }

                // Create order
                $create = Order::create($insert_arr);
                if ($create) {

                    // Update order ID
                    $orderId = $this->make_order_number($create->id);
                    $create->update(['orderId' => $orderId]);

                    // Update order payments
                    OrderPayment::find($paid->id)->update(['fk_order_id' => $create->id]);
                    if ($request->input('use_wallet_balance') == 1 && isset($UserWalletPayment) && $UserWalletPayment) {
                        \App\Model\UserWalletPayment::find($UserWalletPayment->id)->update(['paymentResponse' => $orderId]);

                        OrderPayment::where(['fk_order_id' => $create->id])->update(['paymentResponse' => $create->amount_from_card == 0 ? $orderId : $request->input('paymentResponse')]);
                    }

                    // Update order address
                    $address = UserAddress::find($request->input('address_id'));
                    if ($address) {
                        $order_addr_arr = [
                            'fk_order_id' => $create->id,
                            'name' => $address->name,
                            'mobile' => $address->mobile,
                            'landmark' => $address->landmark,
                            'address_line1' => $address->address_line1,
                            'address_line2' => $address->address_line2,
                            'latitude' => $address->latitude,
                            'longitude' => $address->longitude,
                            'address_type' => $address->address_type, //1:office,2:home,3:others
                        ];
                        OrderAddress::create($order_addr_arr);
                    }

                    // Update order delivery slot 
                    OrderDeliverySlot::create([
                        'fk_order_id' => $create->id,
                        'delivery_date' => $delivery_date,
                        'delivery_time' => $delivery_time, //1:right now,2:later in the day
                        'later_time' => $later_time,
                        'delivery_preference' => $request->input('delivery_preference'),
                        'expected_eta' => $user->expected_eta
                    ]);

                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'An error has been discovered');
                    }
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }

                // Adding cart products to order with Buy for me feature
                $this->add_order_products($create);
                
                // Set order status
                $order_status = 0;
                if ($create->payment_type=="cod" || $paid->status == "success") {
                    $order_confirmed = $this->order_confirmed($create->id);
                    $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
                    if ($order_status>0) {
                        // $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                        $message = "Paid successfully!";
                        $error_code = 200;
                    } else {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                        }
                        $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                        throw new Exception($error_message, 105);
                    }
                } else {
                    $message = $lang == 'ar' ? "انتظار الدفع" : "Payment pending!";
                    $error_code = 200;
                }

                // Send order details
                $order_arr = $this->get_order_arr($lang, $create->id);

                // Send sms to the storekeeper
                $this->send_storekeeper_notification($create);

                // Send vendor new order push notification
                $this->send_vendor_new_order_push_notification($create);

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = [
                    'status' => isset($order_arr) && isset($order_arr["status"]) && $order_arr["status"]>0 ? true : false,
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $tap_id
                ];
            } else {

                $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                $this->error = true;
                $this->status_code = 201;
                $this->message = $message;
                $this->result = [
                    'status' => false,
                    'orderId' => 0, 
                    'order' => [], 
                    'tap_id' => $tap_id
                ];

            }

        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_order_pay(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id', 'charge_id']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_order_bp_tap_pay', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $order_id = $request->input('order_id');
            $charge_id = $request->input('charge_id');
            
            // Get order 
            $order = Order::find($order_id);
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order is not found!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            if ($order->payment_type!='online') {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order payment type is not online!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            // Get charge
            $charge = $this->get_charge($charge_id, $order_id, $cus_ref);

            // SMS content & required variables
            $total_amount = $order->total_amount;
            $amount_from_wallet = $order->amount_from_wallet;
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $status = 'failed';
            
            // Payment successful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                
                // Order payment wallet
                if ($amount_from_wallet>0) {
                    \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                        'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                    ]);
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $amount_from_wallet,
                        'status' => "sucsess",
                        'wallet_type' => 2,
                        'transaction_type' => 'debit',
                        'paymentResponse' => $order_id
                    ]);
                }

                // Order payment update
                $status = 'success';
                $insert_arr = [
                    'amount' => $total_amount,
                    'status' => $status,
                    'paymentResponse' => $charge['response']
                ];
                OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                // Order update
                $order_confirmed = $this->order_confirmed($order_id);
                $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
                if ($order_status>0) {
                    // $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $message = "Paid successfully!";
                    $error_code = 200;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                    }
                    $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                    throw new Exception($error_message, 105);
                }

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {
                
                // Order failed
                $this->order_failed($order_id, $order, $charge);

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = true;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "فشل" : "Failed";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_order_pay_by_token(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id', 'token_id', 'amount']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_order_bp_tap_pay_by_token', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $token_id = $request->input('token_id');
            $order_id = $request->input('order_id');
            $amount = $request->input('amount');
            
            // Get order 
            $order = Order::find($order_id);
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order is not found!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            if ($order->payment_type!='online') {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order payment type is not online!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            // Get charge
            $charge = $this->create_charge_with_token($token_id, $order_id, $cus_ref, $amount);

            // SMS content & required variables
            $total_amount = $order->total_amount;
            $amount_from_wallet = $order->amount_from_wallet;
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $status = 'failed';
            
            // Payment successful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                
                // Order payment wallet
                if ($amount_from_wallet>0) {
                    \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                        'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                    ]);
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $amount_from_wallet,
                        'status' => "sucsess",
                        'wallet_type' => 2,
                        'transaction_type' => 'debit',
                        'paymentResponse' => $order_id
                    ]);
                }

                // Order payment update
                $status = 'success';
                $insert_arr = [
                    'amount' => $total_amount,
                    'status' => $status,
                    'paymentResponse' => $charge['response']
                ];
                OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                // Order update
                $order_confirmed = $this->order_confirmed($order_id);
                $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
                if ($order_status>0) {
                    // $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $message = "Paid successfully!";
                    $error_code = 200;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                    }
                    $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                    throw new Exception($error_message, 105);
                }

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } elseif ($charge && isset($charge['text']) && strtolower($charge['text'])=="initiated") {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "قيد الانتظار" : "Pending";
                $this->result = [
                    'status' => isset($charge) && isset($charge['status']) ? $charge['status'] : false,
                    'response' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'message' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {

                // Order failed
                $this->order_failed($order_id, $order, $charge);

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = true;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "فشل" : "Failed";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_order_pay_by_card_id(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id', 'card_id', 'amount']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_order_bp_tap_pay_by_card_id', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $card_id = $request->input('card_id');
            $order_id = $request->input('order_id');
            $amount = $request->input('amount');
            
            // Get order 
            $order = Order::find($order_id);
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order is not found!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            if ($order->payment_type!='online') {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order payment type is not online!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            // Get charge
            $charge = $this->create_charge_for_card_id($card_id, $order_id, $cus_ref, $amount);

            // SMS content & required variables
            $total_amount = $order->total_amount;
            $amount_from_wallet = $order->amount_from_wallet;
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $status = 'failed';

            // Payment successful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                
                // Order payment wallet
                if ($amount_from_wallet>0) {
                    \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                        'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                    ]);
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $amount_from_wallet,
                        'status' => "sucsess",
                        'wallet_type' => 2,
                        'transaction_type' => 'debit',
                        'paymentResponse' => $order_id
                    ]);
                }

                // Order payment update
                $status = 'success';
                $insert_arr = [
                    'amount' => $total_amount,
                    'status' => $status,
                    'paymentResponse' => $charge['response']
                ];
                OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                // Order update
                $order_confirmed = $this->order_confirmed($order_id);
                $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
                if ($order_status>0) {
                    // $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $message = "Paid successfully!";
                    $error_code = 200;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                    }
                    $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                    throw new Exception($error_message, 105);
                }

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'transaction' => $charge['transaction'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } elseif ($charge && isset($charge['text']) && strtolower($charge['text'])=="initiated") {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "قيد الانتظار" : "Pending";
                $this->result = [
                    'status' => isset($charge) && isset($charge['status']) ? $charge['status'] : false,
                    'response' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'message' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {
                // Order failed
                $this->order_failed($order_id, $order, $charge);

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = true;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "فشل" : "Failed";
                $this->result = [
                    'status' => isset($charge) && isset($charge['status']) ? $charge['status'] : false,
                    'response' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'message' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            }

        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_order_pay_status(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id']);

            $tracking_id = add_tracking_data($this->user_id, 'get_order_pay_status', $request, '');
            
            // Get order from request
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $order = Order::find($request->input('order_id'));
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }

            // Default valuse if not processed
            $order_payment = OrderPayment::where(['fk_order_id'=>$request->input('order_id')])->first();
            if (!$order_payment) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order payment is not active");
                }
                throw new Exception($lang == 'ar' ? "دفع طلبك السابق غير نشط" : "Your previous order payment is not active", 105);
            }

            $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
            $charge_status = false;
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "فشل" : "Failed";
            $this->result = [
                'status' => $charge_status,
                'response' => $order_payment->paymentResponse,
                'message' => $message
            ];

            for ($i=0; $i < 9; $i++) { 

                $order_payment = OrderPayment::where(['fk_order_id'=>$request->input('order_id')])->first();
                if (!$order_payment) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Your previous order payment is not active");
                    }
                    throw new Exception($lang == 'ar' ? "دفع طلبك السابق غير نشط" : "Your previous order payment is not active", 105);
                }

                if ($order_payment->status=="pending") {
                    sleep(10);
                }
                elseif ($order_payment->status=="success") {
                    
                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->id);
                    
                    $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "نجاح" : "Success";
                    $this->result = [
                        'status' => true,
                        'response' => $order_payment->paymentResponse,
                        'message' => $message,
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];
                    break;
                } else {
                    if ($order_payment->paymentResponse) {
                        $response_json = json_decode($order_payment->paymentResponse);
                        if ($response_json && isset($response_json->status)) {
                            if (isset($response_json->response) && isset($response_json->response->message)) {
                                $charge_status = false;
                                if ($response_json->response->message=="Declined, Tap") {
                                    $message = "Your card is blocked temporarily, please use another card..";
                                } else {
                                    $message = $response_json->response->message;
                                }
                            }
                        }
                    }

                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->id);
                    
                    $this->error = true;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "فشل" : "Failed";
                    $this->result = [
                        'status' => $charge_status,
                        'response' => $order_payment->paymentResponse,
                        'message' => $message,
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];
                    break;
                }
            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);

        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_subtotal_forgot_something_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'product_json']);

            $tracking_id = add_tracking_data($this->user_id, 'get_subtotal_forgot_something_order_bp_tap', $request, '');
            
            // Get order from request
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $order = Order::where(['id'=>$request->input('id')])
                            ->where(function ($query) {
                                    $query->where('fk_user_id', '=', $this->user_id)
                                    ->orWhere('bought_by', '=', $this->user_id);
                                })->first();
            
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }
            elseif ($order->status>=3) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is already packed. Please make new order..");
                }
                throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
            }


            // Set price key and price total
            $price_total = 0;
            $price_total_with_quantity_mismatch = 0;
            $cartItemArr = [];
            $cartItemQuantityMismatch = [];
            $cartItemOutofstockArr = [];
            
            // Calculate price total for the selected replacing products
            if ($request->input('product_json') != '') {
                $content_arr = json_decode($request->input('product_json'));

                foreach ($content_arr as $key => $value) {
                    $product = BaseProductStore::join('base_products', 'base_products_store.fk_product_id', '=', 'base_products.id')
                        ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                        ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                        ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                        ->select(
                            'base_products_store.id as fk_product_store_id',
                            'base_products_store.itemcode',
                            'base_products_store.barcode',
                            'base_products_store.unit as product_store_unit',
                            'base_products_store.margin',
                            'base_products_store.product_distributor_price as distributor_price',
                            'base_products_store.product_store_price AS product_price',
                            'base_products_store.stock as product_store_stock',
                            'base_products_store.other_names',
                            'base_products_store.fk_product_id',
                            'base_products_store.fk_store_id',
                            'base_products.*',
                            'A.id as category_id',
                            'A.category_name_en',
                            'A.category_name_ar',
                            'B.id as sub_category_id',
                            'B.category_name_en as sub_category_name_en',
                            'B.category_name_ar as sub_category_name_ar',
                            'brands.id as brand_id',
                            'brands.brand_name_en',
                            'brands.brand_name_ar'
                        )
                        ->where('base_products_store.id', '=', $value->product_store_id)
                        ->first();
                    
                    $value->quantity = $value->product_quantity;
                    
                    // If products are not belongs to the company
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 105);
                    }

                    // Check product out of stock
                    
                    if ($product->product_store_stock > 0 && $product->product_store_stock < $value->quantity) {
                        $cartItemQuantityMismatch[$key] = [
                            'product_id' => $product->id,
                            'product_type' => $product->product_type,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_price, 2),
                            'product_total_price' => (string) number_format($product->product_price, 2)*$value->quantity,
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'product_category_id' => $product->fk_category_id,
                            'product_sub_category_id' => $product->fk_sub_category_id,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? ''
                        ];
                        // Calculate the price and amount
                        $price_total_with_quantity_mismatch += number_format(($product->product_price * $product->product_store_stock), 2);
                    }
                    // For other companies
                    elseif ($product->product_store_stock > 0) {
                        $cartItemArr[$key] = [
                            'product_id' => $product->id,
                            'product_type' => $product->product_type,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_price, 2),
                            'product_total_price' => (string) number_format($product->product_price, 2)*$value->quantity,
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'product_category_id' => $product->fk_category_id,
                            'product_sub_category_id' => $product->fk_sub_category_id,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? ''
                        ];
                        // Calculate the price and amount
                        $price_total += number_format(($product->product_price * $value->product_quantity), 2);
                        $price_total_with_quantity_mismatch += number_format(($product->product_price * $value->product_quantity), 2);
                    } else {
                        $cartItemOutofstockArr[$key] = [
                            'product_id' => $product->id,
                            'product_type' => $product->product_type,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_price, 2),
                            'product_total_price' => (string) number_format($product->product_price, 2)*$value->quantity,
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'product_category_id' => $product->fk_category_id,
                            'product_sub_category_id' => $product->fk_sub_category_id,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? ''
                        ];
                    }

                }

            }
            
            // Get user wallet amount
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $wallet_balance = $user_wallet ? $user_wallet->total_points : "0.00";

            // Check free delivery available
            $delivery_cost = get_delivery_cost($user->ivp,$user->mobile,$user->id);
            $mp_amount_for_free_delivery = get_minimum_purchase_amount_for_free_delivery($user->ivp);
            $order_sub_total = $order->sub_total;
            $order_sub_total_with_forgot_something = $order_sub_total + $price_total_with_quantity_mismatch;

            $free_delivery_applied = false;
            if ($order_sub_total<$mp_amount_for_free_delivery) {
                if ($order_sub_total_with_forgot_something>=$mp_amount_for_free_delivery) {
                    $free_delivery_applied = true;
                    // $price_total_with_quantity_mismatch = $price_total_with_quantity_mismatch - $delivery_cost;
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم احتساب المجموع بنجاح" : "Sub total calculated successfully";
            $this->result = [
                'sub_total' => (string) number_format($price_total, 2),
                'sub_total_with_quantity_mismatch' => (string) number_format($price_total_with_quantity_mismatch, 2),
                'wallet_balance' => $wallet_balance,
                'cart_items' => array_values($cartItemArr),
                'cart_items_outofstock' => array_values($cartItemOutofstockArr),
                'cart_items_quantitymismatch' => array_values($cartItemQuantityMismatch), 
                'tap_id' => $cus_ref,
                'order_sub_total' => $order_sub_total_with_forgot_something,
                'minimum_purchase_amount_for_free_delivery' => $mp_amount_for_free_delivery,
                'delivery_charge' => $delivery_cost,
                'free_delivery_applied' => $free_delivery_applied
            ];
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_forgot_something_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'sub_total']);

            $tracking_id = add_tracking_data($this->user_id, 'make_forgot_something_order_bp_tap', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $msg_to_admin = '';
            $payment_amount_text = '';
            $total_amount = $request->input('sub_total');
            $payment_type = $request->input('payment_type');

            if ($total_amount<=0) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Total_amount is $total_amount QAR");
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            //Test numbners array
            $test_numbers = explode(',', env("TEST_NUMBERS"));

            // Check and confirm order is not invoiced yet
            $parent_order = Order::find($request->input('id'));
            if (!$parent_order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }
            elseif ($parent_order->status>=2) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is already packed. Please make new order..");
                }
                if ($request->input('paymentResponse') && $request->input('paymentResponse')!='') {
                    $paymentResponseArr = json_decode($request->input('paymentResponse'));
                    $payment = getPaymentStatusAndAmountFromMyFatoorah($paymentResponseArr);
                    if ($payment['status']=="success") {
                        $refund_amount = $payment['amount'];
                        $this->makeRefund('wallet', $this->user_id, null, $refund_amount, $paymentResponseArr);
                    }
                }
                throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
            }

            /* Order payment */
            if ($total_amount<=0) {
                $status = 'success';
                $wallet_to_refund = abs($total_amount);
                $insert_arr = [
                    'parent_order_id' => $request->input('id'),
                    'amount' => $total_amount,
                    'status' => 'success'
                ];
                $paid = OrderPayment::create($insert_arr);

                if ($paid && $parent_order->payment_type=='online') {
                    $this->makeRefund('wallet', $user->id, 0, $wallet_to_refund, $request->input('id'));
                    $payment_amount_text .= "Wallet (QAR. $total_amount)";
                } else {
                    $payment_amount_text .= "COD (QAR. $total_amount)";
                }

            }
            elseif ($request->input('use_wallet_balance') == 1) {
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                    $status = 'success';

                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                    $payment_amount_text .= "Wallet (QAR. $total_amount)";
                } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $status = "pending";
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;
    
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $cart_payment_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "Card (QAR. $cart_payment_amount) / Wallet (QAR. $user_wallet->total_points)";
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;

                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $cart_payment_amount,
                            'status' => $status
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "COD (QAR. $cart_payment_amount) / Wallet (QAR. $user_wallet->total_points)";
                    }
                    
                } else {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $status = "pending";
    
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "Card (QAR. $total_amount)";
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount,
                            'status' => $status
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "COD (QAR. $total_amount)";
                    }
                    
                }
            } else {
                if ($payment_type=='online') {
                    // Process cart payment
                    $status = "pending";

                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $request->input('paymentResponse')
                    ];
                    $paid = OrderPayment::create($insert_arr);
                    $payment_amount_text .= "Card (QAR. $total_amount)";
                } else {
                    // Considering all others are COD
                    $status = 'success';
                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => $status
                    ];
                    $paid = OrderPayment::create($insert_arr);
                    $payment_amount_text .= "COD (QAR. $total_amount)";
                }
            }
            if ($paid) {
                
                if($request->input('buy_it_for_me_request_id')){

                    $bought_for = UserBuyItForMeRequest::find($request->input('buy_it_for_me_request_id'));
            
                    $insert_arr = [
                        'fk_user_id' => $bought_for->from_user_id,
                        'bought_by' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'payment_type' => $request->input('payment_type'), //cod or online
                        'delivery_charge' => 0
                    ];
                }else{

                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'payment_type' => $request->input('payment_type'),
                        'delivery_charge' => 0
                    ];
                }

                // Reset delivery charge
                // $insert_arr['delivery_charge'] = $delivery_charge;

                if ($request->input('use_wallet_balance') == 1) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                    if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                        $insert_arr['amount_from_wallet'] = $total_amount;
                        $insert_arr['amount_from_card'] = 0;
                        $amount_from_wallet = $total_amount;
                        // Deduct wallet
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                        $UserWalletPayment = \App\Model\UserWalletPayment::create([
                            'fk_user_id' => $this->user_id,
                            'amount' => $amount_from_wallet,
                            'status' => $status,
                            'wallet_type' => 2,
                            'transaction_type' => 'debit'
                        ]);
                    } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                        $insert_arr['amount_from_wallet'] = $user_wallet->total_points;
                        $insert_arr['amount_from_card'] = $total_amount - $user_wallet->total_points;
                        $amount_from_wallet = $user_wallet->total_points;
                        // Deduct wallet only for COD
                        if ($payment_type=='cod') {
                            \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                                'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                            ]);
                            $UserWalletPayment = \App\Model\UserWalletPayment::create([
                                'fk_user_id' => $this->user_id,
                                'amount' => $amount_from_wallet,
                                'status' => $status,
                                'wallet_type' => 2,
                                'transaction_type' => 'debit'
                            ]);
                        }
                    } else {
                        $insert_arr['amount_from_wallet'] = 0;
                        $insert_arr['amount_from_card'] = $total_amount;

                        $amount_from_wallet = 0;
                    }

                } else {
                    $net_total = $total_amount;
                    $insert_arr['amount_from_wallet'] = 0;
                    $insert_arr['amount_from_card'] = $total_amount;
                }

                $insert_arr['amount_from_card'] = $insert_arr['amount_from_card'];
                $insert_arr['total_amount'] = $total_amount;
                $insert_arr['grand_total'] = $total_amount;
                $insert_arr['status'] = 0;
                $insert_arr['change_for'] = $request->input('payment_type') == "cod" ? $request->input('change_for') : '';
                $insert_arr['parent_id'] = $request->input('id');

                $user = \App\Model\User::find($this->user_id);

                $insert_arr['fk_store_id'] = $user->nearest_store;

                // Check order payment is successful or not
                $insert_arr['status'] = $paid->status == "success" ? 1 : 0;

                // Setup test orders
                if (in_array($user->mobile, $test_numbers)) {
                    $insert_arr['test_order'] = 1;
                } else {
                    $insert_arr['test_order'] = 0;
                }

                // Create sub order and  append products
                $create = Order::create($insert_arr);
                if ($create) {
                    OrderPayment::find($paid->id)->update(['fk_order_id' => $create->id]);

                    $orderId = (1000 + $create->id);
                    Order::find($create->id)->update(['orderId' => $orderId]);

                    // For successful orders only
                    if ($create->status>0) {
                        if ($request->input('use_wallet_balance') == 1) {
                            \App\Model\UserWalletPayment::find($UserWalletPayment->id)->update(['paymentResponse' => $orderId]);
                            OrderPayment::where(['fk_order_id' => $create->id])->update(['paymentResponse' => $orderId]);
                        }
    
                        $order = Order::find($request->input('id'));
    
                        if ($request->input('product_json') != '') {
                            $content_arr = json_decode($request->input('product_json'));
    
                            foreach ($content_arr as $key => $value) {
                                $product = BaseProduct::join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')
                                    ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                                    ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                                    ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                                    ->select(
                                        'base_products_store.id as fk_product_store_id',
                                        'base_products_store.itemcode',
                                        'base_products_store.barcode',
                                        'base_products_store.unit as product_store_unit',
                                        'base_products_store.margin',
                                        'base_products_store.product_distributor_price as distributor_price',
                                        'base_products_store.stock as product_store_stock',
                                        'base_products_store.other_names',
                                        'base_products_store.fk_product_id',
                                        'base_products.*',
                                        'A.id as category_id',
                                        'A.category_name_en',
                                        'A.category_name_ar',
                                        'B.id as sub_category_id',
                                        'B.category_name_en as sub_category_name_en',
                                        'B.category_name_ar as sub_category_name_ar',
                                        'brands.id as brand_id',
                                        'brands.brand_name_en',
                                        'brands.brand_name_ar'
                                    )
                                    ->where('base_products.id', '=', $value->product_id)
                                    ->first();
    
                                if (!$product) {
                                    // $error_message = $lang == 'ar' ? 'المنتج غير متوفر' : 'The product is not available';
                                    // throw new Exception($error_message, 106);
                                    $orderProduct = OrderProduct::create([
                                        'fk_order_id' => $create->id,
                                        'fk_product_id' => $value->product_id,
                                        'fk_product_store_id' => $value->product_store_id ?? 0,
                                        'fk_store_id' => 0,
                                        'single_product_price' => 0,
                                        'total_product_price' => 0,
                                        'itemcode' => '',
                                        'barcode' => '',
                                        'fk_category_id' => 0,
                                        'category_name' => '',
                                        'category_name_ar' => '',
                                        'fk_sub_category_id' => '',
                                        'sub_category_name' => '',
                                        'sub_category_name_ar' => '',
                                        'fk_brand_id' => '',
                                        'brand_name' => '',
                                        'brand_name_ar' => '',
                                        'product_name_en' => '',
                                        'product_name_ar' => '',
                                        'product_image' => '',
                                        'product_image_url' => '',
                                        'margin' => '',
                                        'distributor_price' => 0,
                                        'unit' => '',
                                        '_tags' =>'',
                                        'tags_ar' => '',
                                        'discount' => 0,
                                        'product_quantity' => 0,
                                        'product_weight' => '',
                                        'product_unit' => '',
                                        'sub_products' => ''
                                    ]);
                                    throw new Exception($orderProduct->id, 105);
                                    continue;
                                }
                                
                                $selling_price = number_format($product->product_store_price, 2);
                                $orderProduct = OrderProduct::create([
                                    'fk_order_id' => $request->input('id'),
                                    'fk_product_id' => $value->product_id,
                                    'fk_product_store_id' => $product->fk_product_store_id,
                                    'fk_store_id' => $product->fk_store_id,
                                    'single_product_price' => $selling_price,
                                    'total_product_price' => number_format(($selling_price * $value->product_quantity), 2),
                                    'itemcode' => $product->itemcode ?? '',
                                    'barcode' => $product->barcode ?? '',
                                    'fk_category_id' => $product->fk_category_id ?? '',
                                    'category_name' => $product->category_name_en ?? '',
                                    'category_name_ar' => $product->category_name_ar ?? '',
                                    'fk_sub_category_id' => $product->fk_sub_category_id ?? '',
                                    'sub_category_name' => $product->sub_category_name_en ?? '',
                                    'sub_category_name_ar' => $product->sub_category_name_ar ?? '',
                                    'fk_brand_id' => $product->fk_brand_id ?? '',
                                    'brand_name' => $product->brand_name_en ?? '',
                                    'brand_name_ar' => $product->brand_name_ar,
                                    'product_name_en' => $product->product_name_en ?? '',
                                    'product_name_ar' => $product->product_name_ar ?? '',
                                    'product_image' => $product->product_image ?? '',
                                    'product_image_url' => $product->product_image_url ?? '',
                                    'margin' => $product->margin ?? '',
                                    'distributor_price' => $product->distributor_price ?? '',
                                    'unit' => $product->product_store_unit ?? '',
                                    '_tags' => $product->_tags ?? '',
                                    'tags_ar' => $product->tags_ar ?? '',
                                    'discount' => '',
                                    'product_quantity' => $value->product_quantity,
                                    'product_weight' => $product->product_store_unit ?? '',
                                    'product_unit' => $product->product_store_unit ?? '',
                                    'is_missed_product' => 1
                                ]);
                                
                                // Assign storekeeper based on the products store
                                $this->assign_storekeeper_in_order($order,$orderProduct);
                            }
                        }
    
                        // Send order details
                        $order_arr = $this->get_order_arr($lang, $order->id);
                        
                        // successful message
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,"Paid successfully!");
                        }
                        $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                        $error_code = 200;
                    } else {

                        $order = Order::find($request->input('id'));
    
                        if ($request->input('product_json') != '') {
                            $content_arr = json_decode($request->input('product_json'));
    
                            foreach ($content_arr as $key => $value) {
                                $product = BaseProduct::join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')
                                    ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                                    ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                                    ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                                    ->select(
                                        'base_products_store.id as fk_product_store_id',
                                        'base_products_store.itemcode',
                                        'base_products_store.barcode',
                                        'base_products_store.unit as product_store_unit',
                                        'base_products_store.margin',
                                        'base_products_store.product_distributor_price as distributor_price',
                                        'base_products_store.stock as product_store_stock',
                                        'base_products_store.other_names',
                                        'base_products.*',
                                        'A.id as category_id',
                                        'A.category_name_en',
                                        'A.category_name_ar',
                                        'B.id as sub_category_id',
                                        'B.category_name_en as sub_category_name_en',
                                        'B.category_name_ar as sub_category_name_ar',
                                        'brands.id as brand_id',
                                        'brands.brand_name_en',
                                        'brands.brand_name_ar'
                                    )
                                    ->where('base_products.id', '=', $value->product_id)
                                    ->first();
    
                                if (!$product) {
                                    // $error_message = $lang == 'ar' ? 'المنتج غير متوفر' : 'The product is not available';
                                    // throw new Exception($error_message, 106);
                                    $orderProduct = OrderProduct::create([
                                        'fk_order_id' => $create->id,
                                        'fk_product_id' => $value->product_id,
                                        'fk_product_store_id' => $value->product_store_id,
                                        'fk_store_id' => 0,
                                        'single_product_price' => 0,
                                        'total_product_price' => 0,
                                        'itemcode' => '',
                                        'barcode' => '',
                                        'fk_category_id' => 0,
                                        'category_name' => '',
                                        'category_name_ar' => '',
                                        'fk_sub_category_id' => '',
                                        'sub_category_name' => '',
                                        'sub_category_name_ar' => '',
                                        'fk_brand_id' => '',
                                        'brand_name' => '',
                                        'brand_name_ar' => '',
                                        'product_name_en' => '',
                                        'product_name_ar' => '',
                                        'product_image' => '',
                                        'product_image_url' => '',
                                        'margin' => '',
                                        'distributor_price' => 0,
                                        'unit' => '',
                                        '_tags' =>'',
                                        'tags_ar' => '',
                                        'discount' => 0,
                                        'product_quantity' => 0,
                                        'product_weight' => '',
                                        'product_unit' => '',
                                        'sub_products' => ''
                                    ]);
                                    throw new Exception($orderProduct->id, 105);
                                    continue;
                                }
                                
                                $selling_price = number_format($product->product_store_price, 2);
                                $orderProduct = OrderProduct::create([
                                    'fk_order_id' => $create->id,
                                    'fk_product_id' => $value->product_id,
                                    'fk_product_store_id' => $product->fk_product_store_id,
                                    'fk_store_id' => $product->fk_store_id,
                                    'single_product_price' => $selling_price,
                                    'total_product_price' => number_format(($selling_price * $value->product_quantity), 2),
                                    'itemcode' => $product->itemcode ?? '',
                                    'barcode' => $product->barcode ?? '',
                                    'fk_category_id' => $product->fk_category_id ?? '',
                                    'category_name' => $product->category_name_en ?? '',
                                    'category_name_ar' => $product->category_name_ar ?? '',
                                    'fk_sub_category_id' => $product->fk_sub_category_id ?? '',
                                    'sub_category_name' => $product->sub_category_name_en ?? '',
                                    'sub_category_name_ar' => $product->sub_category_name_ar ?? '',
                                    'fk_brand_id' => $product->fk_brand_id ?? '',
                                    'brand_name' => $product->brand_name_en ?? '',
                                    'brand_name_ar' => $product->brand_name_ar,
                                    'product_name_en' => $product->product_name_en ?? '',
                                    'product_name_ar' => $product->product_name_ar ?? '',
                                    'product_image' => $product->product_image ?? '',
                                    'product_image_url' => $product->product_image_url ?? '',
                                    'margin' => $product->margin ?? '',
                                    'distributor_price' => $product->distributor_price ?? '',
                                    'unit' => $product->product_store_unit ?? '',
                                    '_tags' => $product->_tags ?? '',
                                    'tags_ar' => $product->tags_ar ?? '',
                                    'discount' => '',
                                    'product_quantity' => $value->product_quantity,
                                    'product_weight' => $product->product_store_unit ?? '',
                                    'product_unit' => $product->product_store_unit ?? '',
                                    'is_missed_product' => 1
                                ]);

                                // Assign storekeeper based on the products store
                                $this->assign_storekeeper_in_order($order,$orderProduct);
    
                            }
                        }
    
                        // Send order details
                        $order_arr = $this->get_order_arr($lang, $order->id);
                        
                        // successful message
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,"Payment pending!");
                        }
                        $message = $lang == 'ar' ? "انتظار الدفع" : "Payment pending!";
                        $error_code = 200;

                    }

                    // ---------------------------------------------------------------
                    // Send notification SMS to admins
                    // ---------------------------------------------------------------
                    if (strtolower($paid->status) != "pending") {
                        
                        $parent_orderId = $create->parent_id+1000;
                        $msg_to_admin .= "Forgot Something Order #$parent_orderId - Payment Method: $payment_amount_text.\n\n";
                        $msg_to_admin .= "Payment Status: ".$paid->status;

                        $test_order = $order->test_order;
                        $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                        // SMS to Fleet Manager
                        $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                        if ($send_notification && $send_notification_number) {
                            $msg = $msg_to_admin;
                            $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                        }
                        // SMS to CEO
                        $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                        if ($send_notification && $send_notification_number_2) {
                            $msg = $msg_to_admin;
                            $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                        }
                        // SMS to CTO
                        $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                        if ($send_notification && $send_notification_number_3) {
                            $msg = $msg_to_admin;
                            $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                        }
                        // SMS to Backend Developer
                        $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                        if ($send_notification && $send_notification_number_4) {
                            $msg = $msg_to_admin;
                            $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                        }
                        // SMS to Driver
                        $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                        if ($send_notification && $send_notification_number_5) {
                            $msg = $msg_to_admin;
                            $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                        }
                    }
                    
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"An error has been discovered");
                    }
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }
                    
                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = [
                    'status' => isset($insert_arr) && isset($insert_arr["status"]) && $insert_arr["status"]>0 ? true : false,
                    'forgot_something_order_id' => isset($create) && isset($create->id) ? $create->id : 0, 
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {
                $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                $this->error = true;
                $this->status_code = 201;
                $this->message = $message;
                $this->result = [
                    'status' => false,
                    'orderId' => 0, 
                    'order' => [], 
                    'tap_id' => $cus_ref
                ];

            }
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_forgot_something_order_pay(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id', 'charge_id']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_forgot_something_order_bp_tap_pay', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $order_id = $request->input('order_id');
            $charge_id = $request->input('charge_id');
            
            // Get order 
            $order = Order::find($order_id);
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order is not found!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            if ($order->payment_type!='online') {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order payment type is not online!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            $parent_order = Order::find($order->parent_id);
            if (!$parent_order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }

            // Get charge
            $charge = $this->get_charge($charge_id, $order_id, $cus_ref);

            // SMS content & required variables
            $msg_to_admin = '';
            $payment_amount_text = '';
            $total_amount = $order->total_amount;
            $amount_from_wallet = $order->amount_from_wallet;
            $amount_from_card = $order->amount_from_card;
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $status = 'failed';
            if ($amount_from_wallet>0) {
                $payment_amount_text .= "Card (QAR. $amount_from_card) / Wallet (QAR. $amount_from_wallet)";
            } else {    
                $payment_amount_text .= "Card (QAR. $amount_from_card)";
            }

            // Payment succesful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                
                // Order payment wallet
                if ($amount_from_wallet>0) {
                    \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                        'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                    ]);
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $amount_from_wallet,
                        'status' => "sucsess",
                        'wallet_type' => 2,
                        'transaction_type' => 'debit',
                        'paymentResponse' => $order_id
                    ]);
                }

                // Order payment update
                $status = 'success';
                $insert_arr = [
                    'amount' => $total_amount,
                    'status' => $status,
                    'paymentResponse' => $charge['response']
                ];
                OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                // Order update
                Order::find($order_id)->update(['status'=>1]);

                // Update order products
                OrderProduct::where(["fk_order_id"=>$order->id])->update(["fk_order_id"=>$order->parent_id]);
                
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->parent_id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "نجاح" : "Success";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->parent_id);

                $this->error = true;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "فشل" : "Failed";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            }
            
            // ---------------------------------------------------------------
            // Send notification SMS to admins
            // ---------------------------------------------------------------
            if (strtolower($status) != "pending") {
                
                $parent_orderId = $order->parent_id+1000;
                $msg_to_admin .= "Forgot Something Order #$parent_orderId - Payment Method: $payment_amount_text.\n\n";
                $msg_to_admin .= "Payment Status: ".$charge['text'];

                $test_order = $order->test_order;
                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                // SMS to Fleet Manager
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                if ($send_notification && $send_notification_number) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                }
                // SMS to CEO
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                }
                // SMS to CTO
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                }
                // SMS to Backend Developer
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                }
                // SMS to Driver
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                }

            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_forgot_something_order_pay_by_token(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id', 'token_id', 'amount']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_forgot_something_order_pay_by_token', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $card_id = $request->input('card_id');
            $order_id = $request->input('order_id');
            $amount = $request->input('amount');
            
            // Get order 
            $order = Order::find($order_id);
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order is not found!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            if ($order->payment_type!='online') {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order payment type is not online!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            $parent_order = Order::find($order->parent_id);
            if (!$parent_order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }

            // Get charge
            $charge = $this->create_charge_for_card_id($card_id, $order_id, $cus_ref, $amount, "forgot_something_order");

            // SMS content & required variables
            $msg_to_admin = '';
            $payment_amount_text = '';
            $total_amount = $order->total_amount;
            $amount_from_wallet = $order->amount_from_wallet;
            $amount_from_card = $order->amount_from_card;
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $status = 'failed';
            if ($amount_from_wallet>0) {
                $payment_amount_text .= "Card (QAR. $amount_from_card) / Wallet (QAR. $amount_from_wallet)";
            } else {    
                $payment_amount_text .= "Card (QAR. $amount_from_card)";
            }

            // Payment successful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                
                // Order payment wallet
                if ($amount_from_wallet>0) {
                    \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                        'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                    ]);
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $amount_from_wallet,
                        'status' => "sucsess",
                        'wallet_type' => 2,
                        'transaction_type' => 'debit',
                        'paymentResponse' => $order_id
                    ]);
                }

                // Order payment update
                $status = 'success';
                $insert_arr = [
                    'amount' => $total_amount,
                    'status' => $status,
                    'paymentResponse' => $charge['response']
                ];
                OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                // Order update
                Order::find($order_id)->update(['status'=>1]);

                // Update order products
                OrderProduct::where(["fk_order_id"=>$order->id])->update(["fk_order_id"=>$order->parent_id]);

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->parent_id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "نجاح" : "Success";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } elseif ($charge && isset($charge['text']) && strtolower($charge['text'])=="initiated") {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "قيد الانتظار" : "Pending";
                $this->result = [
                    'status' => isset($charge) && isset($charge['status']) ? $charge['status'] : false,
                    'response' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'message' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->parent_id);

                $this->error = true;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "فشل" : "Failed";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            }
            
            // ---------------------------------------------------------------
            // Send notification SMS to admins
            // ---------------------------------------------------------------
            if (strtolower($status) != "pending") {
                    
                $parent_orderId = $order->parent_id+1000;
                $msg_to_admin .= "Forgot Something Order #$parent_orderId - Payment Method: $payment_amount_text.\n\n";
                $msg_to_admin .= "Payment Status: ".$charge['text'];

                $test_order = $order->test_order;
                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                // SMS to Fleet Manager
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                if ($send_notification && $send_notification_number) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                }
                // SMS to CEO
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                }
                // SMS to CTO
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                }
                // SMS to Backend Developer
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                }
                // SMS to Driver
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                }
            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_forgot_something_order_pay_by_card_id(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id', 'card_id', 'amount']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_forgot_something_order_pay_by_card_id', $request, '');
            
            // Find user, order charge
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $card_id = $request->input('card_id');
            $order_id = $request->input('order_id');
            $amount = $request->input('amount');
            
            // Get order 
            $order = Order::find($order_id);
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order is not found!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            if ($order->payment_type!='online') {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Order payment type is not online!');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            // Get charge
            $charge = $this->create_charge_for_card_id($card_id, $order_id, $cus_ref, $amount, "forgot_something_order");

            // SMS content & required variables
            $msg_to_admin = '';
            $payment_amount_text = '';
            $total_amount = $order->total_amount;
            $amount_from_wallet = $order->amount_from_wallet;
            $amount_from_card = $order->amount_from_card;
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $status = 'failed';
            if ($amount_from_wallet>0) {
                $payment_amount_text .= "Card (QAR. $amount_from_card) / Wallet (QAR. $amount_from_wallet)";
            } else {    
                $payment_amount_text .= "Card (QAR. $amount_from_card)";
            }

            // Payment successful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                
                // Order payment wallet
                if ($amount_from_wallet>0) {
                    \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                        'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                    ]);
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $amount_from_wallet,
                        'status' => "sucsess",
                        'wallet_type' => 2,
                        'transaction_type' => 'debit',
                        'paymentResponse' => $order_id
                    ]);
                }

                // Order payment update
                $status = 'success';
                $insert_arr = [
                    'amount' => $total_amount,
                    'status' => $status,
                    'paymentResponse' => $charge['response']
                ];
                OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                // Order update
                Order::find($order_id)->update(['status'=>1]);

                // Update order products
                OrderProduct::where(["fk_order_id"=>$order->id])->update(["fk_order_id"=>$order->parent_id]);

                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->parent_id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "نجاح" : "Success";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } elseif ($charge && isset($charge['text']) && strtolower($charge['text'])=="initiated") {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->id);

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "قيد الانتظار" : "Pending";
                $this->result = [
                    'status' => isset($charge) && isset($charge['status']) ? $charge['status'] : false,
                    'response' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'message' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            } else {
                // Send order details
                $order_arr = $this->get_order_arr($lang, $order->parent_id);

                $this->error = true;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "فشل" : "Failed";
                $this->result = [
                    'status' => $charge['status'],
                    'response' => $charge['text'],
                    'message' => $charge['text'],
                    'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'tap_id' => $cus_ref
                ];
            }
            
            // ---------------------------------------------------------------
            // Send notification SMS to admins
            // ---------------------------------------------------------------
            if (strtolower($status) != "pending") {
                    
                $parent_orderId = $order->parent_id+1000;
                $msg_to_admin .= "Forgot Something Order #$parent_orderId - Payment Method: $payment_amount_text.\n\n";
                $msg_to_admin .= "Payment Status: ".$charge['text'];

                $test_order = $order->test_order;
                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                // SMS to Fleet Manager
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                if ($send_notification && $send_notification_number) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                }
                // SMS to CEO
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                }
                // SMS to CTO
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                }
                // SMS to Backend Developer
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                }
                // SMS to Driver
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                }
            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_forgot_something_order_pay_status(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['order_id']);

            $tracking_id = add_tracking_data($this->user_id, 'get_forogt_something_order_pay_status', $request, '');
            
            // Get order from request
            $user = \App\Model\User::find($this->user_id);
            $cus_ref = $user->tap_id;
            $order = Order::where(['id'=>$request->input('order_id'),'fk_user_id'=>$this->user_id])->first();
            if (!$order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }
            $parent_order = Order::where(['id'=>$order->parent_id])->first();
            if (!$parent_order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active (parent order)");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }

            // Default valuse if not processed
            $order_payment = OrderPayment::where(['fk_order_id'=>$request->input('order_id')])->first();
            if (!$order_payment) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order payment is not active");
                }
                throw new Exception($lang == 'ar' ? "دفع طلبك السابق غير نشط" : "Your previous order payment is not active", 105);
            }

            $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
            $charge_status = false;
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "فشل" : "Failed";
            $this->result = [
                'status' => $charge_status,
                'response' => $order_payment->paymentResponse,
                'message' => $message
            ];

            for ($i=0; $i < 9; $i++) { 

                $order_payment = OrderPayment::where(['fk_order_id'=>$request->input('order_id')])->first();
                if (!$order_payment) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Your previous order payment is not active");
                    }
                    throw new Exception($lang == 'ar' ? "دفع طلبك السابق غير نشط" : "Your previous order payment is not active", 105);
                }

                if ($order_payment->status=="pending") {
                    sleep(10);
                }
                elseif ($order_payment->status=="success") {
                    
                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->parent_id);

                    $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "نجاح" : "Success";
                    $this->result = [
                        'status' => true,
                        'response' => $order_payment->paymentResponse,
                        'message' => $message,
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];
                    break;
                } else {
                    if ($order_payment->paymentResponse) {
                        $response_json = json_decode($order_payment->paymentResponse);
                        if ($response_json && isset($response_json->status)) {
                            if (isset($response_json->response) && isset($response_json->response->message)) {
                                $charge_status = false;
                                if ($response_json->response->message=="Declined, Tap") {
                                    $message = "Your card is blocked temporarily, please use another card..";
                                } else {
                                    $message = $response_json->response->message;
                                }
                            }
                        }
                    }
                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->parent_id);

                    $this->error = true;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "فشل" : "Failed";
                    $this->result = [
                        'status' => $charge_status,
                        'response' => $order_payment->paymentResponse,
                        'message' => $message,
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];
                    break;
                }
            }
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);

        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function my_orders_opmtized(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $current_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->whereIn('orders.status', array(1, 2, 3, 6))
                ->where('order_payments.status', '=', 'success')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();
            $past_orders = Order::where(function ($query) {
                    $query->where('fk_user_id', '=', $this->user_id)
                    ->orWhere('bought_by', '=', $this->user_id);
                })
                ->where('status', '=', 7)
                ->where('parent_id', '=', 0)
                ->orderBy('id', 'desc')
                ->get();
            $cancelled_orders = Order::where(function ($query) {
                    $query->where('fk_user_id', '=', $this->user_id)
                    ->orWhere('bought_by', '=', $this->user_id);
                })
                ->where('status', '=', 4)
                ->where('parent_id', '=', 0)
                ->orderBy('id', 'desc')
                ->get();

            $past_order_arr = [];
            if ($past_orders->count()) {
                foreach ($past_orders as $value) {
                    if ($lang=='ar' && $value->order_json_ar!=NULL) {
                        $past_order_arr[] = json_decode($value->order_json_ar);
                    } elseif ($value->order_json_en!=NULL) {
                        $past_order_arr[] = json_decode($value->order_json_en);
                    } else {
                        // Get order
                        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                        ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                        ->where('orders.status', '>', 0)
                        ->where('orders.parent_id', '=', 0)
                        ->where('orders.id', '=', $value->id)
                        ->first();
                        // Generate Json
                        if ($order) {
                            $order_arr = [
                                'id' => $order->id,
                                'orderId' => $order->orderId,
                                'sub_total' => $order->getSubOrder ? (string) ($order->sub_total + $order->getOrderSubTotal($order->id)) : (string) $order->sub_total,
                                'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($order->id)) : (string) $order->total_amount,
                                'delivery_charge' => $order->delivery_charge,
                                'coupon_discount' => $order->coupon_discount ?? '',
                                'item_count' => $order->getOrderProducts->count(),
                                'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                                'status' => $order->status,
                                'change_for' => $order->change_for ?? '',
                                'delivery_in' => getDeliveryIn($order->getOrderDeliverySlot->expected_eta ?? 0),
                                'delivery_time' => $order->delivery_time,
                                'later_time' => $order->later_time ?? '',
                                'delivery_preference' => $order->delivery_preference ?? '',
                                'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
                                'bought_by' => $order->bought_by ?? 0
                            ];
                            // Store json
                            $order->update([
                                'order_json_en'=>json_encode($order_arr),
                                'order_json_ar'=>json_encode($order_arr)
                            ]);
                            // Add to array
                            $past_order_arr[] = [
                                'id' => $order->id,
                                'orderId' => $order->orderId,
                                'sub_total' => $order->getSubOrder ? (string) ($order->sub_total + $order->getOrderSubTotal($order->id)) : (string) $order->sub_total,
                                'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($order->id)) : (string) $order->total_amount,
                                'delivery_charge' => $order->delivery_charge,
                                'coupon_discount' => $order->coupon_discount ?? '',
                                'item_count' => $order->getOrderProducts->count(),
                                'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                                'status' => $order->status,
                                'change_for' => $order->change_for ?? '',
                                'delivery_in' => getDeliveryIn($order->getOrderDeliverySlot->expected_eta ?? 0),
                                'delivery_time' => $order->delivery_time,
                                'later_time' => $order->later_time ?? '',
                                'delivery_preference' => $order->delivery_preference ?? '',
                                'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
                                'bought_by' => $order->bought_by ?? 0
                            ];
                        }
                    }
                }
            }

            $current_order_arr = [];
            if ($current_orders->count()) {
                foreach ($current_orders as $key => $value) {
                    $current_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }

            $cancelled_order_arr = [];
            if ($cancelled_orders->count()) {
                foreach ($cancelled_orders as $key => $value) {
                    if ($lang=='ar' && $value->order_json_ar!=NULL) {
                        $cancelled_order_arr[] = json_decode($value->order_json_ar);
                    } elseif ($value->order_json_en!=NULL) {
                        $cancelled_order_arr[] = json_decode($value->order_json_en);
                    } else {
                        // Get order
                        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                        ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                        ->where('orders.status', '>', 0)
                        ->where('orders.parent_id', '=', 0)
                        ->where('orders.id', '=', $value->id)
                        ->first();
                        // Generate Json
                        if ($order) {
                            $order_arr = [
                                'id' => $order->id,
                                'orderId' => $order->orderId,
                                'sub_total' => $order->getSubOrder ? (string) ($order->sub_total + $order->getOrderSubTotal($order->id)) : (string) $order->sub_total,
                                'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($order->id)) : (string) $order->total_amount,
                                'delivery_charge' => $order->delivery_charge,
                                'coupon_discount' => $order->coupon_discount ?? '',
                                'item_count' => $order->getOrderProducts->count(),
                                'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                                'status' => $order->status,
                                'change_for' => $order->change_for ?? '',
                                'delivery_in' => getDeliveryIn($order->getOrderDeliverySlot->expected_eta ?? 0),
                                'delivery_time' => $order->delivery_time,
                                'later_time' => $order->later_time ?? '',
                                'delivery_preference' => $order->delivery_preference ?? '',
                                'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
                                'bought_by' => $order->bought_by ?? 0
                            ];
                            // Store json
                            $order->update([
                                'order_json_en'=>json_encode($order_arr),
                                'order_json_ar'=>json_encode($order_arr)
                            ]);
                            // Add to array
                            $cancelled_order_arr[] = [
                                'id' => $order->id,
                                'orderId' => $order->orderId,
                                'sub_total' => $order->getSubOrder ? (string) ($order->sub_total + $order->getOrderSubTotal($order->id)) : (string) $order->sub_total,
                                'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($order->id)) : (string) $order->total_amount,
                                'delivery_charge' => $order->delivery_charge,
                                'coupon_discount' => $order->coupon_discount ?? '',
                                'item_count' => $order->getOrderProducts->count(),
                                'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                                'status' => $order->status,
                                'change_for' => $order->change_for ?? '',
                                'delivery_in' => getDeliveryIn($order->getOrderDeliverySlot->expected_eta ?? 0),
                                'delivery_time' => $order->delivery_time,
                                'later_time' => $order->later_time ?? '',
                                'delivery_preference' => $order->delivery_preference ?? '',
                                'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
                                'bought_by' => $order->bought_by ?? 0
                            ];
                        }
                    }
                }
            }


            if ($lang == "ar") {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status_ar[0]],
                    ['status' => 1, 'message' => $this->order_status_ar[1]],
                    ['status' => 2, 'message' => $this->order_status_ar[1]],
                    ['status' => 3, 'message' => $this->order_status_ar[1]],
                    ['status' => 4, 'message' => $this->order_status_ar[4]],
                    ['status' => 6, 'message' => $this->order_status_ar[6]],
                    ['status' => 7, 'message' => $this->order_status_ar[7]],
                ];
            } else {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status[0]],
                    ['status' => 1, 'message' => $this->order_status[1]],
                    ['status' => 2, 'message' => $this->order_status[1]],
                    ['status' => 3, 'message' => $this->order_status[1]],
                    ['status' => 4, 'message' => $this->order_status[4]],
                    ['status' => 6, 'message' => $this->order_status[6]],
                    ['status' => 7, 'message' => $this->order_status[7]],
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "طلباتي" : "My Orders";
            $this->result = ['order_status' => $status_arr, 'past' => $past_order_arr, 'current' => $current_order_arr, 'cancelled' => $cancelled_order_arr];
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

    protected function my_orders(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $past_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->where('order_payments.status', '=', 'success')
                ->where('orders.status', '=', 7)
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();
            $current_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->whereIn('orders.status', array(1, 2, 3, 6))
                ->where('order_payments.status', '=', 'success')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();
            $cancelled_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->where('order_payments.status', '=', 'success')
                ->where('orders.status', '=', 4)
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();

            $past_order_arr = [];
            if ($past_orders->count()) {
                foreach ($past_orders as $key => $value) {
                    $past_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }

            $current_order_arr = [];
            if ($current_orders->count()) {
                foreach ($current_orders as $key => $value) {
                    $current_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }

            $cancelled_order_arr = [];
            if ($cancelled_orders->count()) {
                foreach ($cancelled_orders as $key => $value) {
                    $cancelled_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }


            if ($lang == "ar") {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status_ar[0]],
                    ['status' => 1, 'message' => $this->order_status_ar[1]],
                    ['status' => 2, 'message' => $this->order_status_ar[1]],
                    ['status' => 3, 'message' => $this->order_status_ar[1]],
                    ['status' => 4, 'message' => $this->order_status_ar[4]],
                    ['status' => 6, 'message' => $this->order_status_ar[6]],
                    ['status' => 7, 'message' => $this->order_status_ar[7]],
                ];
            } else {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status[0]],
                    ['status' => 1, 'message' => $this->order_status[1]],
                    ['status' => 2, 'message' => $this->order_status[1]],
                    ['status' => 3, 'message' => $this->order_status[1]],
                    ['status' => 4, 'message' => $this->order_status[4]],
                    ['status' => 6, 'message' => $this->order_status[6]],
                    ['status' => 7, 'message' => $this->order_status[7]],
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "طلباتي" : "My Orders";
            $this->result = ['order_status' => $status_arr, 'past' => $past_order_arr, 'current' => $current_order_arr, 'cancelled' => $cancelled_order_arr];
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

    protected function order_detail(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $order = Order::leftJoin('orders as ord', 'ord.parent_id', '=', 'orders.id')
                ->select('orders.*')
                ->selectRaw("SUM(ord.amount_from_wallet) as total_amount_from_wallet")
                ->selectRaw("SUM(ord.amount_from_card) as total_amount_from_card")
                ->where('orders.id', '=', $request->input('id'))
                ->first();
            if (!$order || $order->id==NULL) {
                throw new Exception("Order doesn't exist", 105);
            }
            
            // Store
            $store = Store::where(['id' => $order->fk_store_id, 'status' => 1, 'deleted' => 0])->first();
            if ($store) {
                $store_no = get_store_no($store->name);
                $company_id = $store->company_id;
            } else {
                $store_no = 0;
                $company_id = 0;
            }
            
            // Set delivery time
            $orderDelivery = $order->getOrderDeliverySlot;
            if ($orderDelivery && $orderDelivery->delivery_time==1) {
                $delivery_in = getDeliveryInTimeRange($orderDelivery->expected_eta, $orderDelivery->created_at, $lang);
            } else {
                // Get the sheduled earliest time
                // $later_time = strtok($orderDelivery->later_time, '-');
                // $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                // $later_delivery_time = $orderDelivery->delivery_date.' '.$later_time.':00';
                // $delivery_in = getDeliveryInTimeRange(0, $later_delivery_time, $lang);
                
                $later_time_arr = explode('-', $orderDelivery->later_time);
                $later_time_start = is_array($later_time_arr) && isset($later_time_arr[0]) ? date('h:ia',strtotime($later_time_arr[0])) : "";
                $later_time_end = is_array($later_time_arr) && isset($later_time_arr[1]) ? date('h:ia',strtotime($later_time_arr[1])) : "";
                $delivery_in = $later_time_start.'-'.$later_time_end;
            }

            //Expected arrival time
            if($orderDelivery->delivery_time == 1){
                $expected_arrival_time = get_delivery_time_in_text($orderDelivery->expected_eta, 'i');
            }else{
                
                $dateString = $orderDelivery->delivery_date;
                $dateToCheck = \Carbon\Carbon::parse($dateString);
                $currentDate = \Carbon\Carbon::now();

                $date = "";
                // Check if it's today, yesterday, or tomorrow
                if ($dateToCheck->isSameDay($currentDate)) {
                    $date =  $lang =='ar' ? 'اليوم' : "Today";
                } elseif ($dateToCheck->isSameDay($currentDate->addDay())) {
                    $date =  $lang =='ar' ? 'بكرا' : "Tomorrow";
                }else{
                    $date = $dateString;
                }
                
                $split_arrival_time = explode('-',$orderDelivery->later_time);
                $expected_arrival_time = date("g:i A", strtotime($split_arrival_time[0])).' - '.date("g:i A", strtotime($split_arrival_time[1])).' ('.$date.')';
            }
            

            $order_arr = [
                'id' => $order->id,
                'orderId' => $order->orderId,
                'sub_total' => $order->getSubOrder ? (string) ($order->sub_total + $order->getOrderSubTotal($request->input('id'))) : (string) $order->sub_total,
                'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($request->input('id'))) : (string) $order->total_amount,
                'delivery_charge' => $order->getSubOrder ? (string) ($order->delivery_charge + $order->getOrderDeliveryCharge($request->input('id'))) : (string) ($order->delivery_charge),
                'coupon_discount' => $order->coupon_discount ?? '',
                'item_count' => $order->getOrderProducts->count(),
                'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                'status' => $order->status,
                'delivery_date' => $order->getOrderDeliverySlot->delivery_date ?? "",
                'change_for' => $order->change_for ?? '',
                'amount_from_wallet' => $order->amount_from_wallet ? (string) ($order->amount_from_wallet + $order->total_amount_from_wallet) : '0',
                'amount_from_card' => number_format($order->total_amount_from_card, 2),
                'delivery_in' => $delivery_in,
                'delivery_time' => $order->delivery_time,
                'later_time' => $order->later_time,
                'delivery_preference' => $order->delivery_preference,
                'store_id' => $order->fk_store_id,
                'store' => 'store'.$store_no,
                'company_id' => $company_id,
                'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
                'bought_by' => $order->bought_by ?? 0,
                'forgot_something_store_id' => env("FORGOT_SOMETHING_STORE_ID"),
                'expected_arrival_time' => $expected_arrival_time,
                'product_filter_tag' => 'fbt'
            ];

            $order_products = OrderProduct::leftJoin('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                ->select('base_products.*', 'order_products.product_unit', 'order_products.product_unit', 'order_products.product_image_url', 'order_products.product_weight', 'order_products.total_product_price', 'order_products.single_product_price', 'order_products.product_quantity', 'order_products.discount', 'order_products.is_out_of_stock', 'order_products.sub_products')
                ->where('order_products.fk_order_id', '=', $request->input('id'))
                ->where('order_products.deleted', '=', 0)
                // ->where('order_products.is_out_of_stock', '=', 0)
                ->get();

            $item_arr = [];
            $item_outofstock_arr = [];
            $item_n = 0;
            $item_outofstock_n = 0;
            if ($order_products->count()) {
                foreach ($order_products as $key => $value) {
                    // If sub products for recipe
                    $sub_product_arr = [];
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = json_decode($value->sub_products);
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key2 => $value2) {
                                if ($lang == 'ar') {
                                    $sub_product_arr[$key2] = array (
                                        'product_id' => $value2->product_id ? (int) $value2->product_id : 0,
                                        'product_quantity' => $value2->product_quantity ? (int) $value2->product_quantity : 0,
                                        'product_name' => $value2->product_name_ar ?? '',
                                        'product_image' => $value2->product_image_url ?? '',
                                        'product_price' => $value2->price ?? 0,
                                        'item_unit' => $value2->unit ?? ''
                                    );
                                } else {
                                    $sub_product_arr[$key2] = array (
                                        'product_id' => $value2->product_id ? (int) $value2->product_id : 0,
                                        'product_quantity' => $value2->product_quantity ? (int) $value2->product_quantity : 0,
                                        'product_name' => $value2->product_name_ar ?? '',
                                        'product_image' => $value2->product_image_url ?? '',
                                        'product_price' => $value2->price ?? 0,
                                        'item_unit' => $value2->unit ?? ''
                                    );
                                }
                            }
                        }
                    }
                    // Set product unit
                    // if ($value->product_unit == 'gr' && $value->product_weight >= 1000) {
                    //     $product_weight = ($value->product_weight / 1000) . ' kg';
                    // } else {
                    //     $product_weight =  $value->product_unit ? round($value->product_weight) . ' ' . $value->product_unit : $value->product_weight;
                    // }
                    $product_weight = preg_replace("/ gr$/", '', $value->product_unit);
                    if (str_ends_with($value->product_unit, ' gr') && is_numeric($product_weight) && $product_weight >= 1000) {
                        $product_weight = ($product_weight / 1000) . ' kg';
                    } else {
                        $product_weight =  $value->product_unit;
                    }
                    if ($value->is_out_of_stock==1) {
                        $item_outofstock_arr[$item_outofstock_n] = [
                            'product_id' => $value->id,
                            'type' => $value->product_type,
                            'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                            'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                            'unit' => $value->product_unit,
                            'quantity' => $product_weight,
                            'product_price' => $value->total_product_price,
                            'single_product_price' => $value->single_product_price,
                            'product_discount' => $value->discount,
                            'product_quantity' => $value->product_quantity,
                            'in_stock' => 0,
                            'sub_products' => $sub_product_arr
                        ];
                        $item_outofstock_n++;
                    } else {
                        $item_arr[$item_n] = [
                            'product_id' => $value->id,
                            'type' => $value->product_type,
                            'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                            'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                            'unit' => $value->product_unit,
                            'quantity' => $product_weight,
                            'product_price' => $value->total_product_price,
                            'single_product_price' => $value->single_product_price,
                            'product_discount' => $value->discount,
                            'product_quantity' => $value->product_quantity,
                            'in_stock' => 1,
                            'sub_products' => $sub_product_arr
                        ];
                        $item_n++;
                    }
                }
            } 

            $order_driver = OrderDriver::where('fk_order_id', '=', $request->input('id'))
                ->where('fk_driver_id', '!=', 0)
                ->first();

            if ($order_driver) {
                $driver_arr = [
                    'id' => $order_driver->fk_driver_id,
                    'driver_name' => $order_driver->getDriver->name,
                    'driver_email' => $order_driver->getDriver->email,
                    'driver_image' => !empty($order_driver->getDriver->getDriverImage) ? asset('images/common_images') . '/' . $order_driver->getDriver->getDriverImage->file_name : '',
                    'mobile' => $order_driver->getDriver->country_code . $order_driver->getDriver->mobile,
                ];
            } else {
                $driver_arr = (object) [];
            }

            $orderAdr = OrderAddress::where(['fk_order_id' => $request->input('id')])->first();
            if ($orderAdr) {
                $order_addr_arr = $orderAdr;
            } else {
                $order_addr_arr = (object) [];
            }
            $status_arr = [];
            for ($i = 0; $i <= count($this->order_status); $i++) {
                if ($i != 5) {
                    $status = OrderStatus::where('fk_order_id', '=', $request->input('id'))
                        ->where('status', '=', $i)
                        ->first();

                    if ($status) {
                        $checked = 1;
                        $datetime = date('Y-m-d H:i:s', strtotime($status->created_at));
                    } else {
                        $checked = 0;
                        $datetime = '';
                    }
                    $status_arr[$i] = [
                        'status' => $i,
                        'message' => $lang == "ar" ? $this->order_status_ar[$i] : $this->order_status[$i],
                        'datetime' => $datetime,
                        'checked' => $checked,
                    ];
                }
            }
            // Update order statud out for delivery if it is not tracked
            if (isset($status_arr[7]) && $status_arr[7]['datetime']!='' && isset($status_arr[6]) && $status_arr[6]['datetime']=='') {
                $delivered_time = $status_arr[7]['datetime'];
                $datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime($delivered_time)));
                $status_arr[6]['datetime'] = $datetime;
                $status_arr[6]['checked'] = 1;
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "تفاصيل الطلب" : "Order detail";
            $this->result = [
                'order_detail' => $order_arr,
                'order_items' => $item_arr,
                'order_outofstock_items' => $item_outofstock_arr,
                'order_address' => $order_addr_arr,
                'order_status' => array_values($status_arr),
                'assigned_driver' => $driver_arr
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

    protected function rate_order(Request $request)
    {
        try {

            $tracking_id = add_tracking_data($this->user_id, 'rate_order', $request, '');
            
            $lang = $request->header('lang');
            if($request->input('skip') == 1){
                $this->required_input($request->input(), ['id']);
            }else{
                $this->required_input($request->input(), ['id', 'rating', 'review']);
            }

            // $reviewed = OrderReview::where(['fk_user_id' => $this->user_id, 'fk_order_id' => $request->input('id')])->first();
            // if ($reviewed) {
            //     $error_message = $lang == 'ar' ? "استعرضت هذا الطلب بالفعل" : "Already reviewed this order";
            //     if (isset($tracking_id) && $tracking_id) {
            //         update_tracking_response($tracking_id,$error_message);
            //     }
            //     throw new Exception($error_message, 105);
            // }

            $rating = $request->input('rating');
            $rating_arr = is_array($rating) ? $rating : json_decode($rating, true);
            if($rating_arr){ 
                $products_quality = isset($rating_arr['products_quality']) ? $rating_arr['products_quality'] : '';
                $delivery_experience = isset($rating_arr['delivery_experience']) ? $rating_arr['delivery_experience'] : '';
                $overall_satisfaction = isset($rating_arr['overall_satisfaction']) ? $rating_arr['overall_satisfaction'] : '';
                if (is_array($rating_arr) && $products_quality=='') {
                    foreach ($rating_arr as $key => $value) {
                        $products_quality = $products_quality=='' && isset($value['products_quality']) ? $value['products_quality'] : '';
                        $delivery_experience = $delivery_experience=='' && isset($value['delivery_experience']) ? $value['delivery_experience'] : '';
                        $overall_satisfaction = $overall_satisfaction=='' && isset($value['overall_satisfaction']) ? $value['overall_satisfaction'] : '';
                    }
                }
                $update = OrderReview::create([
                    'fk_user_id' => $this->user_id,
                    'fk_order_id' => $request->input('id'),
                    'review' => $request->input('review'),
                    'products_quality' => $products_quality,
                    'delivery_experience' => $delivery_experience,
                    'overall_satisfaction' => $overall_satisfaction,
                    'skip' => $request->input('skip')
                ]);
            }else{
                $update = OrderReview::create([
                    'fk_user_id' => $this->user_id,
                    'fk_order_id' => $request->input('id'),
                    'skip' => $request->input('skip')
                ]);
            }

            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $request->input('skip') == 1 ? ($lang=='ar' ? "تمت المراجعة بنجاح!" : "Order Review Skipped!") : ($lang=='ar' ? "تمت المراجعة بنجاح!" : "Reviewed successfully!");
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,$error_message);
                }
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function reorder(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['id']);

            $order = Order::find($request->input('id'));
            if (!$order) {
                $error_message = $lang=='ar' ? "الطلب غير موجود" : "Order doesn't exist";
                throw new Exception($error_message, 105);
            }

            $user = User::find($this->user_id);
            $user->nearest_store = 14;
            
            $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
            if ($nearest_store) {
                $default_store_no = get_store_no($nearest_store->name);
                $company_id = $nearest_store->company_id;
            } else {
                $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                throw new Exception($error_message, 106);
            }
            $product_price_key = 'store' . $default_store_no . '_price';
            if ($order->getOrderProducts->count()) {
                foreach ($order->getOrderProducts as $key => $value) {
                    $product = BaseProduct::select(
                        'base_products.id',
                        'base_products.fk_product_store_id',
                        'base_products.fk_store_id',
                        'base_products.product_store_price',
                        'base_products.product_store_stock',
                    )
                    ->where('base_products.id', '=', $value->fk_product_id)
                    ->first();
                    
                    $selling_price = number_format($product->product_store_price, 2);

                    $product_exist = UserCart::where(['fk_product_id' => $value->fk_product_id, 'fk_user_id' => $this->user_id])->first();
                    
                    if ($product_exist) {
                        $insert_arr = [
                            'fk_user_id' => $this->user_id,
                            'fk_product_id' => $value->fk_product_id,
                            'fk_product_store_id' => $value->fk_product_store_id,
                            'quantity' => $value->product_quantity+$product_exist->quantity,
                            'total_price' => ($selling_price * $value->product_quantity),
                            'total_discount' => '',
                            'weight' => $value->product_weight,
                            'unit' => $value->product_unit
                        ];

                        $product_exist->update($insert_arr);

                    }else{

                        $insert_arr = [
                            'fk_user_id' => $this->user_id,
                            'fk_product_id' => $value->fk_product_id,
                            'fk_product_store_id' => $value->fk_product_store_id,
                            'quantity' => $value->product_quantity,
                            'total_price' => ($selling_price * $value->product_quantity),
                            'total_discount' => '',
                            'weight' => $value->product_weight,
                            'unit' => $value->product_unit
                        ];

                        UserCart::create($insert_arr);
                    }
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم الإضافة الى السلة" : "Ordered items added into cart!";
            } else {
                $error_message = $lang=='ar' ? "لا يوجد منتج موجود بالترتيب" : "No product exist in the order";
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

    protected function cancel_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id']);

            $order = Order::find($request->input('id'));
            if (!$order) { 
                $error_message = $lang=='ar' ? "الطلب غير موجود" : "Order doesn't exist";
                throw new Exception($error_message, 105);
            }
            if ($order->status == 4) {
                $error_message = $lang=='ar' ? "تم إلغاء الطلب بالفعل" : "Order already cancelled";
                throw new Exception($error_message, 105);
            }

            if ($order->status == 0) {
                $error_message = $lang=='ar' ? "لم يتم تأكيد الطلب" : "Order is not confirmed, please contact support";
                throw new Exception($error_message, 105);
            }

            $order_payment = OrderPayment::where("fk_order_id","=",$request->input('id'))->first();
            if (!$order_payment || $order_payment->status!="success") { 
                $error_message = $lang=='ar' ? "لم يكن دفع الطلب ناجحًا" : "Order payment was not successful";
                throw new Exception($error_message, 105);
            }

            if ($order->status == 1) {
                
                // Refund to wallet if pay by cart or wallet
                $order_cancelled = $this->order_cancelled($order->id, $order, 'user');
                $order_status = $order_cancelled && isset($order_cancelled['order_status']) ? $order_cancelled['order_status'] : 0;
                if ($order_status==4) {
                    $wallet_to_refund = $order_cancelled && isset($order_cancelled['wallet_to_refund']) ? $order_cancelled['wallet_to_refund'] : 0;
                    if ($wallet_to_refund>0) {
                        $message = $lang=='ar' ? "تم إلغاء الطلب وتم إعادة الأموال إلى محفظتك" : "Order cancelled and refund has been credited into your wallet";
                    } else {
                        $message = $lang=='ar' ? "تم الغاء الطلب" : "Order cancelled!";
                    }
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                    }
                    $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                    throw new Exception($error_message, 105);
                }

            } else {
                $error_message = $lang=='ar' ? "لا يمكنك إلغاء الطلب الآن" : "You cannot cancel order now";
                throw new Exception($error_message, 105);
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
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

    protected function filter_orders(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $filter_status = $request->input('status');
            $filter_date_from = $request->input('date_from');
            $filter_date_to = $request->input('date_to');

            //setting the page, limit and offset
            if ($request->input('page') == '') {
                $page = 1;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }
            }
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where('order_payments.status', '=', 'success')
                ->where('orders.parent_id', '=', 0)
                ->where(function ($query) {
                    $query->where('orders.fk_user_id', '=', $this->user_id)
                    ->orWhere('orders.bought_by', '=', $this->user_id);
                })
                ->orderBy('orders.id', 'desc')
                ->offset($offset)
                ->limit($limit);

            if(isset($filter_status)){
                $orders->where('orders.status', '=', $filter_status);
            }
            
            if((isset($filter_date_from) && isset($filter_date_to))){
                $orders->whereBetween('orders.created_at',[$filter_date_from,$filter_date_to]);
            }

            $orders = $orders->get();

            $order_arr = [];
            if ($orders->count()) {
                foreach ($orders as $key => $value) {
                    $order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }

            if ($lang == "ar") {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status_ar[0]],
                    ['status' => 1, 'message' => $this->order_status_ar[1]],
                    ['status' => 2, 'message' => $this->order_status_ar[1]],
                    ['status' => 3, 'message' => $this->order_status_ar[1]],
                    ['status' => 4, 'message' => $this->order_status_ar[4]],
                    ['status' => 6, 'message' => $this->order_status_ar[6]],
                    ['status' => 7, 'message' => $this->order_status_ar[7]],
                ];
            } else {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status[0]],
                    ['status' => 1, 'message' => $this->order_status[1]],
                    ['status' => 2, 'message' => $this->order_status[1]],
                    ['status' => 3, 'message' => $this->order_status[1]],
                    ['status' => 4, 'message' => $this->order_status[4]],
                    ['status' => 6, 'message' => $this->order_status[6]],
                    ['status' => 7, 'message' => $this->order_status[7]],
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "طلباتي" : "Orders";
            $this->result = ['order_status' => $status_arr, 'orders' => $order_arr];
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

    protected function filter_order_products(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $filter_status = $request->input('status');
            $filter_date_from = $request->input('date_from');
            $filter_date_to = $request->input('date_to');

            //setting the page, limit and offset
            if ($request->input('page') == '') {
                $page = 1;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }
            }
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $order_products = OrderProduct::leftJoin('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                ->leftJoin('orders', 'order_products.fk_order_id', '=', 'orders.id')
                ->select('order_products.*')
                ->where(function ($query) {
                    $query->where('fk_user_id', '=', $this->user_id)
                    ->orWhere('bought_by', '=', $this->user_id);
                })
                ->orderBy('order_products.id', 'desc')
                ->offset($offset)
                ->limit($limit);

            if(isset($filter_status)){
                $order_products->where('orders.status', '=', $filter_status);
            }

            if((isset($filter_date_from) && isset($filter_date_to))){
                $order_products->whereBetween('order_products.created_at',[$filter_date_from,$filter_date_to]);
            }

            $order_products = $order_products->get();

            $order_product_arr = [];
            if ($order_products->count()) {
                foreach ($order_products as $key => $value) {
                    $order_product_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->fk_order_id,
                        'product_id' => $value->id,
                        'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                        'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                        'unit' => $value->product_unit,
                        'product_price' => $value->total_product_price,
                        'single_product_price' => $value->single_product_price,
                        'product_discount' => $value->discount,
                        'product_quantity' => $value->product_quantity,
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->order_date)),
                        'sub_category_name' => $lang == 'ar' ? $value->sub_category_name_ar : $value->sub_category_name,
                        'sub_category_name' => $lang == 'ar' ? $value->brand_name_ar : $value->brand_name
                    ];
                }
            }

            if ($lang == "ar") {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status_ar[0]],
                    ['status' => 1, 'message' => $this->order_status_ar[1]],
                    ['status' => 2, 'message' => $this->order_status_ar[1]],
                    ['status' => 3, 'message' => $this->order_status_ar[1]],
                    ['status' => 4, 'message' => $this->order_status_ar[4]],
                    ['status' => 6, 'message' => $this->order_status_ar[6]],
                    ['status' => 7, 'message' => $this->order_status_ar[7]],
                ];
            } else {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status[0]],
                    ['status' => 1, 'message' => $this->order_status[1]],
                    ['status' => 2, 'message' => $this->order_status[1]],
                    ['status' => 3, 'message' => $this->order_status[1]],
                    ['status' => 4, 'message' => $this->order_status[4]],
                    ['status' => 6, 'message' => $this->order_status[6]],
                    ['status' => 7, 'message' => $this->order_status[7]],
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "طلباتي" : "Orders";
            $this->result = ['order_status' => $status_arr, 'orders' => $order_product_arr];
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
