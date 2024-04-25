<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\ProductTag;
use App\Model\BaseProduct;
use App\Model\TagBundle;
use App\Model\SearchTag;
use Algolia\AlgoliaSearch\SearchClient;

use DB;

class ProductTagController extends CoreApiController
{

    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $tags = ProductTag::orderBy('id', 'desc')
                ->where('title_en', 'like', '%' . $filter . '%')
                ->paginate(100);
        } else {
            $tags = ProductTag::orderBy('id', 'desc')
                ->paginate(100);
        }
        return view('admin.product_tags.index', [
            'tags' => $tags,
            'filter' => $filter
        ]);
    }

    public function create(Request $request)
    {
        $tag_bundles = TagBundle::all();
        return view('admin.product_tags.create',[
            'tag_bundles' => $tag_bundles
        ]);
    }

    public function store(Request $request)
    {

        $fk_tag_bundle_id = $request->input('fk_tag_bundle_id')==NULL ? 0 : $request->input('fk_tag_bundle_id');

        $insert_arr = [
            'title_en' => $request->input('title_en') ? $request->input('title_en') : '',
            'title_ar' => $request->input('title_ar') ? $request->input('title_ar') : '',
            'tag' => $request->input('title_en') ? str_replace(' ', '_', strtolower($request->input('title_en'))) : '',
            'fk_tag_bundle_id' => $fk_tag_bundle_id
        ];
        
        if ($request->hasFile('image')) {
            $tag_image_path = str_replace('\\', '/', storage_path("app/public/images/tags/"));
            $tag_image_url_base = "storage/images/tags/"; 

            $path = "/images/tags/";
            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('image')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['tag_image'] = env('APP_URL').$tag_image_url_base . $fileNameToStore;
            endif;
        }

        if ($request->hasFile('banner_image')) {
            $banner_image_path = str_replace('\\', '/', storage_path("app/public/images/tags/"));
            $banner_image_url_base = "storage/images/tags/"; 

            $path = "/images/tags/";
            $filenameWithExt = $request->file('banner_image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('banner_image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('banner_image')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['banner_image'] = env('APP_URL').$banner_image_url_base . $fileNameToStore;
            endif;
        }

        $add = ProductTag::create($insert_arr);
        if ($add) {
            return back()->with('success', 'Tag added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding tag');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $tag = ProductTag::find($id);
        $tag_bundles = TagBundle::all();

        return view('admin.product_tags.edit', [
            'tag' => $tag,
            'tag_bundles' => $tag_bundles
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $tag = ProductTag::find($id);

        if ($tag) {

            $fk_tag_bundle_id = $request->input('fk_tag_bundle_id')==NULL ? 0 : $request->input('fk_tag_bundle_id');

            $update_arr = [
                'title_en' => $request->input('title_en'),
                'title_ar' => $request->input('title_ar'),
                'tag' => str_replace(' ', '_', strtolower($request->input('title_en'))),
                'fk_tag_bundle_id' => $fk_tag_bundle_id
            ];
            
            if ($request->hasFile('image')) {
                $tag_image_path = str_replace('\\', '/', storage_path("app/public/images/tags/"));
                $tag_image_url_base = "storage/images/tags/"; 
    
                $path = "/images/tags/";
                $filenameWithExt = $request->file('image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                $check = $request->file('image')->storeAs('public/'.$path,$fileNameToStore);
                if ($check) :
                    $update_arr['tag_image'] = env('APP_URL').$tag_image_url_base . $fileNameToStore;
                endif;
            }
    
            if ($request->hasFile('banner_image')) {
                $banner_image_path = str_replace('\\', '/', storage_path("app/public/images/tags/"));
                $banner_image_url_base = "storage/images/tags/"; 
    
                $path = "/images/tags/";
                $filenameWithExt = $request->file('banner_image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('banner_image')->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                $check = $request->file('banner_image')->storeAs('public/'.$path,$fileNameToStore);
                if ($check) :
                    $update_arr['banner_image'] = env('APP_URL').$banner_image_url_base . $fileNameToStore;
                endif;
            }
    
            $update = ProductTag::find($id)->update($update_arr);
            if ($update) {
                return back()->with('success', 'Tag updated successfully');
            }
        }

        return back()->withInput()->with('error', 'Error while updating tag');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $tag = ProductTag::find($id);
        if ($tag) {
            ProductTag::find($id)->delete();
            return redirect('admin/product_tags')->with('success', 'Tag deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting tag');
        }
    }

    public function create_multiple(Request $request)
    {
        return view('admin.product_tags.create_multiple');
    }

    //Product tags bulk upload
    protected function bulk_upload(Request $request)
    {
        $path = "/product_files/";
        $file = $this->uploadFile($request, 'product_tags_csv', $path);

        $product_tags = csvToArray(public_path('/product_files/') . $file);
        if ($product_tags) {

            foreach ($product_tags as $key => $value) {

                // Check tag already exist
                $product_tag_exist = ProductTag::where('title_en',trim($value[0]))->first();

                if(!$product_tag_exist){
                    
                    $tag = str_replace(' ', '_', strtolower(trim($value[0])));
                    
                    $insert_arr = [
                        'title_en' => trim($value[0]),
                        'title_ar' => trim($value[1]),
                        'tag' => $tag
                    ];

                    $create = ProductTag::create($insert_arr);

                    if ($create) {
                        \Log::info('Product tag created '.$value[0]);
                    } else {
                        \Log::info('Product tag not created: '.$value[0]);
                    }
                    
                }
            }

            return redirect('admin/base_products/product_tags/create_multiple')->with('success', 'Product Tags added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }
    
    public function search_products(Request $request)
    {
        $search_tags = $request->query('search_tags') ? rtrim($request->query('search_tags'),',') : '';
        $fk_product_id = $request->query('fk_product_id') ? rtrim($request->query('fk_product_id'),',') : '';
        $without_stock = $request->query('without_stock')==1 ? true : false;
        $search_tags = $search_tags!='' ? explode(',',$search_tags) : [];
        $search_tags_str = '';
        $search_tags_arr = [];

        $client = SearchClient::create(env('ALGOLIA_APP_ID'), env('ALGOLIA_SECRET'));
        $index = $client->initIndex(env('ALGOLIA_PRODUCT_INDEX'));

        // Filters
        if ($without_stock) {
            $numericFilters = [];
        } else {
            $numericFilters = [
                    [
                    "product_store_stock > 0"
                    ],
                    [
                    "fk_product_store_id != 0"
                    ],
                    [
                    "product_store_price != 0.0"
                    ]
                ];
        }

        // Get by product names
        if ($fk_product_id) {
            // Get results from Algolia
            $search_tag = [];
            $search_tag['tag']=$fk_product_id;
            $products = $index->search("", [
                "numericFilters" => [
                    "id=$fk_product_id"
                ]
            ]);
            $search_tag['products']=$products && isset($products['hits']) ? $products['hits'] : [];
            $search_tags_arr[] = $search_tag;
        }

        // Search products
        if ($search_tags) {
            foreach ($search_tags as $key => $tag) {
                // Limit to 20 search tags at a time
                if ($key>=20) {
                    break;
                }
                $search_tags_str .= $tag.',';
                // Update to search tags table
                $search_tag_exists = SearchTag::where(['tag'=>$tag])->first();
                if ($search_tag_exists) {
                    $search_tag_exists->update([
                        'search_count' => ($search_tag_exists->search_count+1)
                    ]);
                } else {
                    SearchTag::create([
                        'tag' => $tag,
                        'search_count' => 1
                    ]);
                }
                // Get results from Algolia
                $search_tag = [];
                $search_tag['tag']=$tag;
                $products = $index->search("$tag", [
                    "numericFilters" => $numericFilters
                ]);
                $search_tag['products']=$products && isset($products['hits']) ? $products['hits'] : [];
                $search_tags_arr[] = $search_tag;
            }  
        } 
        // echo implode(',',$search_tags_arr[0]['products'][0]['_tags']);
        // dd();
        // dd($search_tags_arr);

        // Get old search tags
        $old_search_tags = SearchTag::orderBy('id','desc')->limit(20)->get();

        return view('admin.product_tags.search_products', [
            'search_tags_str' => $search_tags_str,
            'search_tags' => $search_tags_arr,
            'old_search_tags' => $old_search_tags,
            'without_stock' => $without_stock,
            'fk_product_id' => $fk_product_id
        ]);
    }

    protected function search_tags_load_more(Request $request)
    {
        $id = $request->input('id');
        $offset = $id *20;
        $old_search_tags = SearchTag::orderBy('id','desc')->offset($offset)->limit(20)->get();
        $old_search_tags_str = '';
        if ($old_search_tags) {
            foreach ($old_search_tags as $value) {
                $old_search_tags_str .= $value->tag.',';
            }
        }
        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product tags received successfully', 'data' => $old_search_tags_str]);        
    }

    // Select2 Ajax (remote data) pagination
    protected function get_tags(Request $request)
    {
        if ($request->ajax()) {

            $term = trim($request->term);
            $posts = ProductTag::select("id",DB::raw("CONCAT(title_en,',',title_ar) AS text"))
                ->where('title_en', 'LIKE',  '%' . $term. '%')
                ->orderBy('title_en', 'asc')->simplePaginate(5);

            $morePages=true;
            $pagination_obj = json_encode($posts);
            if (empty($posts->nextPageUrl())){
                $morePages=false;
            }
            $results = array(
                "results" => $posts->items(),
                "pagination" => array(
                    "more" => $morePages
                )
            );

            return \Response::json($results);
        }      
    }

    protected function search_products_update(Request $request)
    {
        $id = $request->input('id');
        $search_tag = $request->input('tag');
        $empty_add = $request->input('empty_add');
        $empty_tag = '';
        $product = BaseProduct::find($id);
        $search_tag_exist = false;
        $empty_tag_exist = false;
        if ($product) {
            $tags = $product->_tags;
            $new_tags_arr = [$empty_tag];
            $new_tags_arr_without_empty_tag = [];
            $tags_arr = explode(",",$tags);
            if($tags_arr && $search_tag){
                foreach ($tags_arr as $key=>$tag) {
                    $tag = strtolower(trim($tag));
                    $new_tags_arr[] = $tag;
                    if ($tag!='') {
                        $new_tags_arr_without_empty_tag[] = $tag;
                    }
                    // Check tag already exist
                    if ($tag==$search_tag) {
                        $search_tag_exist = true;
                    }
                    if ($key==0 && $tag=='') {
                        $empty_tag_exist = true;
                    }
                }
            }

            // Add empty tag
            if ($empty_add=='1' || $empty_add==1) {
                if(!$empty_tag_exist) {
                    
                    $tags = implode(",",$new_tags_arr);
                    $update_arr = [
                        '_tags' => $tags??''
                    ];
                    $update = $product->update($update_arr);
                    
                    if ($update) {
                        $product = BaseProduct::find($id);
                        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product tag updated successfully', 'data' => $product]);
                    } else {
                        return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product tag']);
                    }
    
                } else {
                    return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is already having empty tag']);
                }
            }
            // Remove empty tag
            if ($empty_add=='0' || $empty_add==0) {
                if($empty_tag_exist) {
                    
                    $tags = implode(",",$new_tags_arr_without_empty_tag);
                    $update_arr = [
                        '_tags' => $tags??''
                    ];
                    $update = $product->update($update_arr);
                    
                    if ($update) {
                        $product = BaseProduct::find($id);
                        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product tag updated successfully', 'data' => $product]);
                    } else {
                        return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product tag']);
                    }
    
                } else {
                    return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is not having empty tag']);
                }
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is not found!']);
        }
    }

    protected function tags_arrange_save(Request $request)
    {
        $id = $request->input('id');
        $arranged_tags = $request->input('arranged_tags');
        $tags_arr = [];
        $product = BaseProduct::find($id);
        if ($product) {
            if($arranged_tags){
                foreach ($arranged_tags as $key=>$tag) {
                    if (str_contains($tag, 'App\Model')) { 
                        continue;
                    }
                    $tag = strtolower(trim($tag));
                    $tags_arr[] = $tag;
                }
            }

            // Update new tags
            $tags = implode(",",$tags_arr);
            $update_arr = [
                '_tags' => $tags??''
            ];
            $update = $product->update($update_arr);
            
            if ($update) {
                $product = BaseProduct::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product tag updated successfully', 'data' => $product]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product tag']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is not found!']);
        }
    }

}
