<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\TagBundle;
use App\Model\ProductTag;
use App\Model\ProductTagBundle;

class TagBundleController extends CoreApiController
{

    public function index(Request $request)
    {
        $bundles = TagBundle::with('getBundleTags.getProductTags')->orderBy('id', 'desc')->get();
        return view('admin.tag_bundles.index', ['bundles' => $bundles]);
    }

    public function create(Request $request)
    {
        $product_tags = ProductTag::orderBy('id', 'desc')->get();
        return view('admin.tag_bundles.create',['product_tags' => $product_tags]);
    }

    public function store(Request $request)
    {
        $insert_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
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

        $add = TagBundle::create($insert_arr);
        if ($add) {

            $bundle_tags = $request->input('bundle_tags');

            foreach ($bundle_tags as $bundle_tag) {
                $insert_tag_arr = [
                    'fk_bundle_id' => $add->id,
                    'fk_product_tag_id' => $bundle_tag,
                ];

                ProductTagBundle::create($insert_tag_arr);
            }
            
            return redirect('admin/tag_bundles')->with('success', 'Bundle added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding tag');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $bundle = TagBundle::find($id);
        $product_tags = ProductTag::orderBy('id', 'desc')->get();
        $product_tag_bundles = ProductTagBundle::where('fk_bundle_id', $id)->get()->pluck('fk_product_tag_id')->toArray();
        return view('admin.tag_bundles.edit', ['bundle' => $bundle,'product_tags'=>$product_tags,'product_tag_bundles'=>$product_tag_bundles]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $bundle = TagBundle::find($id);

        $update_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
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

        $update = TagBundle::find($id)->update($update_arr);
        if ($update) {
            ProductTagBundle::where(['fk_bundle_id' => $id])->delete();

            $bundle_tags = $request->input('bundle_tags');

            foreach ($bundle_tags as $bundle_tag) {
                $insert_tag_arr = [
                    'fk_bundle_id' => $id,
                    'fk_product_tag_id' => $bundle_tag,
                ];

                ProductTagBundle::create($insert_tag_arr);
            }

            return redirect('admin/tag_bundles')->with('success', 'Bundle updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating tag');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $bundle_tags = TagBundle::find($id);
        if ($bundle_tags) {
            ProductTagBundle::where('fk_bundle_id',$id)->delete();
            TagBundle::find($id)->delete();
            return redirect('admin/tag_bundles')->with('success', 'Bundle deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting tag');
        }
    }
}
