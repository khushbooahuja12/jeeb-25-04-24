<?php

namespace App\Jobs;

use App\Model\User;
use App\Model\ScratchCard;
use App\Model\ScratchCardUser;
use App\Model\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUserScratchCard extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $user_id;
    protected $scratch_card_id;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(int $user_id, int $scratch_card_id)
    {
        $this->user_id = $user_id;
        $this->scratch_card_id = $scratch_card_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get user and scratch card
        $user = User::find($this->user_id);
        $scratch_card = ScratchCard::where(['id'=>$this->scratch_card_id,'deleted'=>0])->first();
        if ($user && $scratch_card) {
            if ($scratch_card->scratch_card_type==0 && $scratch_card->type==1) {
                \Log::error('ProcessUserScratchCard is OnSpot percentage, only applicaple for orders for '.$this->user_id.' and '.$this->scratch_card_id);
            } else {
                // Add Scratch Card to user as active and unscractched
                $scratch_card_arr = $scratch_card->toArray();
                $scratch_card_arr['id'] = NULL;
                $scratch_card_arr['fk_user_id'] = $user->id;
                $scratch_card_arr['fk_scratch_card_id'] = $scratch_card->id;
                $scratch_card_arr['fk_order_id'] = 0;
                $scratch_card_arr['status'] = 1;
                if ($scratch_card->scratch_card_type==1) {
                    $scratch_card_arr['coupon_code'] = 'SC'.$user->id.time();
                }
                // Set expiry date
                $expiry_date=date('Y-m-d');
                $expiry_in = intval($scratch_card->expiry_in);
                if ($expiry_in>0) {
                    $expiry_date = strtotime("+$expiry_in day", strtotime($expiry_date));
                    $expiry_date=date('Y-m-d',$expiry_date);
                }
                $scratch_card_arr['expiry_date'] = $expiry_date;
                $scratch_card_added = ScratchCardUser::create($scratch_card_arr);
                \Log::error('ProcessUserScratchCard: added for '.$this->user_id.' and '.$this->scratch_card_id.' (Added Scratch Card User ID:'.$scratch_card_added->id.')');
            }
        } else {
            \Log::error('ProcessUserScratchCard or User not found for '.$this->user_id.' and '.$this->scratch_card_id);
        }
    }
    
}