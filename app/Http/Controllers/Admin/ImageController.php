<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;

require __DIR__ . "../../../../../vendor/autoload.php";

use App\Model\HomeStatic;

class ImageController extends CoreApiController
{
    public function __construct(Request $request)
    {
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function index(Request $request)
    {   
        $image_path = str_replace('\\', '/', storage_path("app/public/images_uploaded/"));
        $image_url_base = env('APP_URL')."storage/images_uploaded"; 
        $files = [];
        if (is_dir($image_path)) {
            $files = scandir($image_path, SCANDIR_SORT_DESCENDING);
        }
        return view('admin.image.index',['image_url_base'=>$image_url_base,'files'=>$files]);
    }

    protected function upload_image_store(Request $request)
    {

        $request->validate([
            'image_file' => 'required|mimes:webp|max:2048'
        ]);

        if ($request->hasFile('image_file')) {

            // $image_path = str_replace('\\', '/', storage_path("app/public/images_uploaded/"));
            // $image_url_base = "images_uploaded/"; 

            $file_name = time().'_image_file.webp';
            $path = \Storage::putFileAs('public/images_uploaded/', $request->file('image_file'),$file_name);

            return back()->withInput()->with('error', 'Image uploaded successfully');
        
        } 

        return redirect('admin/upload_image')->with('success', 'No image selected');

    }

}
