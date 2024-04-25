<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RetailMartUpdateSingleStoreStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RetailMart:UpdateSingleStoreStock {id?} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update single store stock based on the API and store ID';

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
        // Constatnt RetialMart ID from DB
        $retailmart_id = env("RETAILMART_ID");
        // Store ID from store
        $id = $this->argument('id', null);
		// Call API
		try {
            // Get store details
            $store = \App\Model\Store::where(['id' => $id, 'company_id' => $retailmart_id, 'deleted' => 0, 'status' => 1])->first();

            if ($store) {
                // Generate API Call
                $from_time =  ($store->last_api_updated_at && $store->last_api_updated_at!='') ? $store->last_api_updated_at : '1-Oct-22_00_00_00'; //25-May-21_00_00_00
                $to_time =  date("d-M-y_H_i_s"); //29-May-21_00_00_00

                $api_full_url = $store->api_url;
                $api_full_url = str_replace("[FROM_TIME]",$from_time,$api_full_url);
                $api_full_url = str_replace("[TO_TIME]",$to_time,$api_full_url);
                // echo $api_full_url;

                // API Call
                $response = Http::withOptions([
                        'debug' => true,
                        'verify' => false,
                    ])
                    ->withHeaders([
                        'x-access-key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2NvZGUiOjI0MjUzNSwibHRjX2NvbXBfY29kZSI6IjAwMSIsImlhdCI6MTY0NDM5MzAxNywiZXhwIjoxNjc1OTI5MDE3fQ.IPnPuDmdlnUbOeaN7ssdTUuxOGTa5q2oLGKn5sX1Xlw'
                    ])
                    ->accept('application/json')
                    ->get($api_full_url);
                if ($response->status()==200) {
                    $result = json_decode($response->getBody()->getContents(), true);
                    $data = $result['data'][0];
                    \Log::info(count($data).' products found!');
                    // var_dump($result);

                    // Update the store data for each product
                    foreach ($data as $key => $api_product) {
                        $product = \App\Model\Product::where(['itemcode' => $api_product['SKU_ID'], 'fk_company_id' => $retailmart_id, 'deleted' => 0])->first();
                        if ($product) {
                            // Store 1
                            if ($store->name=='Store 1' && $api_product['PLI_RATE']>0) {
                                $store1_diff = $product->store1_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store1_diff < 0)) {
                                    $store1_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store1_price_arr['margin'] = $store1_insert_arr[1];
                                    $store1_price_arr['product_price'] = $store1_insert_arr[0];
                                } else {
                                    $store1_profit = abs($api_product['PLI_RATE'] - $product->store1_price);
                                    $store1_margin = number_format((($store1_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store1_price_arr['margin'] = $store1_margin;
                                    $store1_price_arr['product_price'] = $product->store1_price;
                                }
                                $stock = [
                                    'store1_distributor_price' => $api_product['PLI_RATE'],
                                    'store1_price' => $store1_price_arr['product_price'],
                                    'store1' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 2
                            if ($store->name=='Store 2' && $api_product['PLI_RATE']>0) {
                                $store2_diff = $product->store2_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store2_diff < 0)) {
                                    $store2_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store2_price_arr['margin'] = $store2_insert_arr[1];
                                    $store2_price_arr['product_price'] = $store2_insert_arr[0];
                                } else {
                                    $store2_profit = abs($api_product['PLI_RATE'] - $product->store2_price);
                                    $store2_margin = number_format((($store2_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store2_price_arr['margin'] = $store2_margin;
                                    $store2_price_arr['product_price'] = $product->store2_price;
                                }
                                $stock = [
                                    'store2_distributor_price' => $api_product['PLI_RATE'],
                                    'store2_price' => $store2_price_arr['product_price'],
                                    'store2' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 3
                            if ($store->name=='Store 3' && $api_product['PLI_RATE']>0) {
                                $store3_diff = $product->store3_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store3_diff < 0)) {
                                    $store3_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store3_price_arr['margin'] = $store3_insert_arr[1];
                                    $store3_price_arr['product_price'] = $store3_insert_arr[0];
                                } else {
                                    $store3_profit = abs($api_product['PLI_RATE'] - $product->store3_price);
                                    $store3_margin = number_format((($store3_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store3_price_arr['margin'] = $store3_margin;
                                    $store3_price_arr['product_price'] = $product->store3_price;
                                }
                                $stock = [
                                    'store3_distributor_price' => $api_product['PLI_RATE'],
                                    'store3_price' => $store3_price_arr['product_price'],
                                    'store3' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 4
                            if ($store->name=='Store 4' && $api_product['PLI_RATE']>0) {
                                $store4_diff = $product->store4_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store4_diff < 0)) {
                                    $store4_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store4_price_arr['margin'] = $store4_insert_arr[1];
                                    $store4_price_arr['product_price'] = $store4_insert_arr[0];
                                } else {
                                    $store4_profit = abs($api_product['PLI_RATE'] - $product->store4_price);
                                    $store4_margin = number_format((($store4_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store4_price_arr['margin'] = $store4_margin;
                                    $store4_price_arr['product_price'] = $product->store4_price;
                                }
                                $stock = [
                                    'store4_distributor_price' => $api_product['PLI_RATE'],
                                    'store4_price' => $store4_price_arr['product_price'],
                                    'store4' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 5
                            if ($store->name=='Store 5' && $api_product['PLI_RATE']>0) {
                                $store5_diff = $product->store5_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store5_diff < 0)) {
                                    $store5_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store5_price_arr['margin'] = $store5_insert_arr[1];
                                    $store5_price_arr['product_price'] = $store5_insert_arr[0];
                                } else {
                                    $store5_profit = abs($api_product['PLI_RATE'] - $product->store5_price);
                                    $store5_margin = number_format((($store5_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store5_price_arr['margin'] = $store5_margin;
                                    $store5_price_arr['product_price'] = $product->store5_price;
                                }
                                $stock = [
                                    'store5_distributor_price' => $api_product['PLI_RATE'],
                                    'store5_price' => $store5_price_arr['product_price'],
                                    'store5' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 6
                            if ($store->name=='Store 6' && $api_product['PLI_RATE']>0) {
                                $store6_diff = $product->store6_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store6_diff < 0)) {
                                    $store6_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store6_price_arr['margin'] = $store6_insert_arr[1];
                                    $store6_price_arr['product_price'] = $store6_insert_arr[0];
                                } else {
                                    $store6_profit = abs($api_product['PLI_RATE'] - $product->store6_price);
                                    $store6_margin = number_format((($store6_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store6_price_arr['margin'] = $store6_margin;
                                    $store6_price_arr['product_price'] = $product->store6_price;
                                }
                                $stock = [
                                    'store6_distributor_price' => $api_product['PLI_RATE'],
                                    'store6_price' => $store6_price_arr['product_price'],
                                    'store6' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 7
                            if ($store->name=='Store 7' && $api_product['PLI_RATE']>0) {
                                $store7_diff = $product->store7_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store7_diff < 0)) {
                                    $store7_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store7_price_arr['margin'] = $store7_insert_arr[1];
                                    $store7_price_arr['product_price'] = $store7_insert_arr[0];
                                } else {
                                    $store7_profit = abs($api_product['PLI_RATE'] - $product->store7_price);
                                    $store7_margin = number_format((($store7_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store7_price_arr['margin'] = $store7_margin;
                                    $store7_price_arr['product_price'] = $product->store7_price;
                                }
                                $stock = [
                                    'store7_distributor_price' => $api_product['PLI_RATE'],
                                    'store7_price' => $store7_price_arr['product_price'],
                                    'store7' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 8
                            if ($store->name=='Store 8' && $api_product['PLI_RATE']>0) {
                                $store8_diff = $product->store8_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store8_diff < 0)) {
                                    $store8_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store8_price_arr['margin'] = $store8_insert_arr[1];
                                    $store8_price_arr['product_price'] = $store8_insert_arr[0];
                                } else {
                                    $store8_profit = abs($api_product['PLI_RATE'] - $product->store8_price);
                                    $store8_margin = number_format((($store8_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store8_price_arr['margin'] = $store8_margin;
                                    $store8_price_arr['product_price'] = $product->store8_price;
                                }
                                $stock = [
                                    'store8_distributor_price' => $api_product['PLI_RATE'],
                                    'store8_price' => $store8_price_arr['product_price'],
                                    'store8' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 9
                            if ($store->name=='Store 9' && $api_product['PLI_RATE']>0) {
                                $store9_diff = $product->store9_price - $api_product['PLI_RATE'];
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store9_diff < 0)) {
                                    $store9_insert_arr = calculatePriceFromFormula($api_product['PLI_RATE']);
                                    $store9_price_arr['margin'] = $store9_insert_arr[1];
                                    $store9_price_arr['product_price'] = $store9_insert_arr[0];
                                } else {
                                    $store9_profit = abs($api_product['PLI_RATE'] - $product->store9_price);
                                    $store9_margin = number_format((($store9_profit / $api_product['PLI_RATE']) * 100), 2);
                                    $store9_price_arr['margin'] = $store9_margin;
                                    $store9_price_arr['product_price'] = $product->store9_price;
                                }
                                $stock = [
                                    'store9_distributor_price' => $api_product['PLI_RATE'],
                                    'store9_price' => $store9_price_arr['product_price'],
                                    'store9' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                            // Store 10
                            if ($store->name=='Store 10' && $api_product['PLI_RATE']>0) {
                                $store10_diff = $product->store10_price - $product->store10_distributor_price;
                                if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $store10_diff < 0)) {
                                    $store10_insert_arr = calculatePriceFromFormula($product->store10_distributor_price);
                                    $store10_price_arr['margin'] = $store10_insert_arr[1];
                                    $store10_price_arr['product_price'] = $store10_insert_arr[0];
                                } else {
                                    $store10_profit = abs($product->store10_distributor_price - $product->store10_price);
                                    $store10_margin = number_format((($store10_profit / $product->store10_distributor_price) * 100), 2);
                                    $store10_price_arr['margin'] = $store10_margin;
                                    $store10_price_arr['product_price'] = $product->store10_price;
                                }
                                $stock = [
                                    'store10_distributor_price' => $api_product['PLI_RATE'],
                                    'store10_price' => $store10_price_arr['product_price'],
                                    'store10' => $api_product['STK_UNCONF_QTY'],
                                ];
                                \App\Model\Product::find($product->id)->update($stock);
                                \Log::info($api_product['SKU_ID'].' product('.$product->id.') updated!');
                            }
                        } else {
                            \Log::info($api_product['SKU_ID'].' not found in products!');
                        }
                    }
                    \App\Model\Store::find($store->id)->update(['last_api_updated_at'=>$to_time]);
                    \Log::info('Updated store '.$id.' with the API call to '.$api_full_url);
                } else {
                    \Log::error("API response does not return 200, returned status code :: " . $response->status() );
                }
            } else {
            	\Log::error("Store Error :: store id ($id) is not found or it is not under company ID (2)");
		    }
		} catch(ConnectException $e) {
			\Log::error("Connection Error :: " .$api_full_url . " " . $e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
		} catch (\Exception $e) {
			\Log::error("HTTP Get Error :: " .$api_full_url . " " . $e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
		}
        return 0;
    }
}
