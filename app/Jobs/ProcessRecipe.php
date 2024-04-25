<?php

namespace App\Jobs;

use App\Model\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecipe extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $key;
    protected $id;
    protected $value;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(string $key, int $id, string $value)
    {
        $this->key = $key;
        $this->id = $id;
        $this->value = trim($value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get recipe
        $recipe = Recipe::find($this->id);
        if ($recipe) {
            $recipe->update([$this->key=>$this->value]);
            \Log::error('ProcessRecipeSingleColumn_'.$this->key.': updatred for '.$this->id.' as '.$this->value);
        } else {
            \Log::error('ProcessRecipeSingleColumn_'.$this->key.': not found for '.$this->id.' as '.$this->value);
        }
    }
    
}