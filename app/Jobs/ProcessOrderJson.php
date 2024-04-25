<?php

namespace App\Jobs;

use App\Model\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderJson extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $order_id;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get order
        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
        ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
        ->where('orders.status', '>', 0)
        ->where('orders.parent_id', '=', 0)
        ->where('orders.id', '=', $this->order_id)
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
            \Log::error('ProcessOrderJson-'.$this->order_id.': updatred');
        } else {
            \Log::error('ProcessOrderJson-'.$this->order_id.': not found');
        }
    }
    
}