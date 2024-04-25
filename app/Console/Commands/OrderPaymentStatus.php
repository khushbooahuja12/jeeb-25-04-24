<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderPaymentStatus extends Command {

    use \App\Http\Traits\PushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:orderpaymentstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating order payment status after confirming from myfatoorah';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $orderPayments = \App\Model\OrderPayment::where('created_at', '>=', \Carbon\Carbon::now()->subMinutes(5))
                ->where('status', '=', 'pending')
                ->get();
        if ($orderPayments->count()) {
            foreach ($orderPayments as $key => $value) {
                $user_id = $value->getOrder->fk_user_id;

                if (empty($value->paymentResponse)) {
                    //mark this order payment as rejected
                    \App\Model\OrderPayment::find($value->id)->update(['status' => 'rejected']);

                    $title = 'Payment failed';
                    $body = 'Payment failed for order #' . $value->getOrder->orderId ?? '';

                    $data = [
                        'status' => 0,
                        'order_id' => $value->fk_order_id,
                        'user_id' => (string) $value->getOrder->fk_user_id,
                        'orderId' => $value->getOrder->orderId ?? ''
                    ];

                    $this->notify_user_about_payment_status($user_id, $title, $body, $data);

                    return false;
                }
                $resArr = json_decode($value->paymentResponse);
                if (empty($resArr->InvoiceId)) {
                    //mark this order payment as rejected
                    \App\Model\OrderPayment::find($value->id)->update(['status' => 'rejected']);

                    $title = 'Payment failed';
                    $body = 'Payment failed for order #' . $value->getOrder->orderId ?? '';

                    $data = [
                        'status' => 0,
                        'order_id' => $value->fk_order_id,
                        'user_id' => (string) $value->getOrder->fk_user_id,
                        'orderId' => $value->getOrder->orderId ?? ''
                    ];

                    $this->notify_user_about_payment_status($user_id, $title, $body, $data);

                    return false;
                }

                $apiURL = 'https://api.myfatoorah.com/v2/';
                $apiKey = 'XYgwpDLlIL3BVm_dvAFepg55uKVdKlz--1bMhtO4qhyAZYFVeqO51O4IPHTwahBXPI-FfA4W0Q3m5QCm9JZ4Ql-Q3LuIPpK88TomNQL7L4X2jCOHRJVMsETrjUsdlYU65p2ET8SaoBbTQbGupAn611tAGlLFNnHaNVo4wbUZwGwJsjGnNBfpEnNZwIRN-6UTzRbvc3lLjQzC5lhIyA-Ai6NymOuWHFRFRwb9LNd96yHp_Z6QhWhPDVJYzSys4ICfCQuKRZ71o1xzlMtpIHaLMkugiiH9Nuu3H_ODjntxtSroC61kpB93-Mj6uJ_L-eOKDSOF4hu88NpO1MgtTQvcENm1FWGVUynKnIbCLfS26c8ScObJcZseiCEtmI_O514GP6qj0a7JscE5l5aSpGE1HgbFtJpkvUmW31OzpJ7AJuyQTNj-nOnfOoR8hcM1FhTx2IU5i9e6Q4Q7IPbYagPWYgQjyMVvsBD-XdtgaILwxIkGz2uMi3dRnCUSwqMAvw9OWD56Pahi_ezdwIoyyujestMBcA615qVJ7SsQfCac6o3B3QYYlzFY0l2UbDfxggG5CN0gwWqulPDkfImEP2mHuZImfVtvNZaC48kjQ32X4IyOZePnjxCI2TmEjCZgwptstFALFdrqBOtUtEHVtHQUOZa6ZdEI2dG3NpQGIbqNh8gGEt6i';

                $endpointURL = $apiURL . 'getPaymentStatus';

                $postFields = [
                    'Key' => $resArr->InvoiceId,
                    'KeyType' => "invoiceId"
                ];

                //Call endpoint
                $res = callAPI($endpointURL, $apiKey, $postFields);

                switch ($res->Data->InvoiceStatus) {
                    case "Pending":
                        if ($res->Data->InvoiceTransactions[0]->ErrorCode != '') {
                            \App\Model\OrderPayment::find($value->id)->update(['status' => 'rejected']);

                            $title = 'Payment failed';
                            $body = 'Payment failed for order #' . $value->getOrder->orderId ?? '';

                            $data = [
                                'status' => 1,
                                'order_id' => $value->fk_order_id,
                                'user_id' => (string) $value->getOrder->fk_user_id,
                                'orderId' => $value->getOrder->orderId ?? ''
                            ];

                            $this->notify_user_about_payment_status($user_id, $title, $body, $data);
                        } else {
                            \App\Model\OrderPayment::find($value->id)->update(['status' => 'pending']);
                        }
                        break;
                    case "Paid":
                        \App\Model\OrderPayment::find($value->id)->update(['status' => 'success']);

                        $title = 'Payment confirmed';
                        $body = 'Payment confirmed for order #' . $value->getOrder->orderId ?? '';

                        $data = [
                            'status' => 1,
                            'order_id' => $value->fk_order_id,
                            'user_id' => (string) $value->getOrder->fk_user_id,
                            'orderId' => $value->getOrder->orderId ?? ''
                        ];

                        $this->notify_user_about_payment_status($user_id, $title, $body, $data);

                        break;
                    case "Canceled":
                        \App\Model\OrderPayment::find($value->id)->update(['status' => 'rejected']);

                        $title = 'Payment failed';
                        $body = 'Payment failed for order #' . $value->getOrder->orderId ?? '';

                        $data = [
                            'status' => 1,
                            'order_id' => $value->fk_order_id,
                            'user_id' => (string) $value->getOrder->fk_user_id,
                            'orderId' => $value->getOrder->orderId ?? ''
                        ];

                        $this->notify_user_about_payment_status($user_id, $title, $body, $data);

                        break;
                    default:
                        \App\Model\OrderPayment::find($value->id)->update(['status' => 'rejected']);
                }
            }
        }
    }

    function notify_user_about_payment_status($user_id, $title, $body, $data) {
        $notification_arr = [
            'fk_user_id' => $user_id,
            'notification_type' => 1,
            'notification_title_en' => $title,
            'notification_title_ar' => '',
            'notification_text_en' => $body,
            'notification_text_ar' => '',
            'data' => $data
        ];
        \App\Model\UserNotification::create($notification_arr);

        $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $user_id])->first();
        if ($user_device) {
            $type = 1;
            $title = $title;
            $body = $body;

            $this->send_push_notification_to_user($user_device->device_token, $type, $title, $body, $data);
        }
    }

}
