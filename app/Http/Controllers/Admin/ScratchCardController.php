<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\ScratchCard;
use App\Model\ScratchCardUser;
use App\Model\Brand;
use App\Model\Category;
use App\Model\Coupon;
use App\Jobs\UpdateUserScratchCard;

class ScratchCardController extends CoreApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter');

        $scratch_cards = false;
        if (!empty($filter)) {
            $scratch_cards = ScratchCard::where('deleted', 0)
                ->where('title_en', 'like', '%' . $filter . '%')
                ->whereOr('title_ar', 'like', '%' . $filter . '%')
                ->orderBy('apply_on', 'desc')
                ->orderBy('apply_on_min_amount', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(5);
            $scratch_cards->appends(['filter' => $filter]);
        } 
        $scratch_cards_on_register = ScratchCard::where('deleted', 0)
            ->where('apply_on', '=', 'register')
            ->orderBy('apply_on', 'desc')
            ->orderBy('apply_on_min_amount', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20, ['*'], 'register');
        $scratch_cards_on_order = ScratchCard::where('deleted', 0)
            ->where('apply_on', '=', 'order')
            ->orderBy('apply_on', 'desc')
            ->orderBy('apply_on_min_amount', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20, ['*'], 'order');

        return view('admin.scratch_cards.index', [
            'scratch_cards_on_register' => $scratch_cards_on_register, 
            'scratch_cards_on_order' => $scratch_cards_on_order, 
            'scratch_cards' => $scratch_cards, 
            'filter' => $filter
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_users(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $scratch_cards = ScratchCardUser::where('deleted', 0)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('id', 'desc')
                ->paginate(50);
        } else {
            $scratch_cards = ScratchCardUser::where('deleted', 0)
                ->orderBy('id', 'desc')
                ->paginate(50);
        }
        $scratch_cards->appends(['filter' => $filter]);

        return view('admin.scratch_cards.index_users', ['scratch_cards' => $scratch_cards, 'filter' => $filter]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brands = Brand::where('deleted', '=', 0)
            ->orderBy('brand_name_en', 'asc')
            ->get();

        $category = Category::where('parent_id', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.scratch_cards.create', ['brands' => $brands, 'category' => $category]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->input('apply_on')=='register' && $request->input('scratch_card_type')==0) {
            return back()->withInput()->with('error', 'On register, on spot reward / scratch card is not applicable!');
        }
        $insert_arr = [
            'apply_on' => $request->input('apply_on'),
            'apply_on_min_amount' => $request->input('apply_on_min_amount') ?? 0.00,
            'scratch_card_type' => $request->input('scratch_card_type'),
            'type' => $request->input('type'),
            'min_amount' => $request->input('min_amount'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'scratch_card_code' => $request->input('scratch_card_code'),
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
            'discount' => $request->input('discount'),
            'expiry_in' => $request->input('expiry_in'),
            'uses_limit' => $request->input('uses_limit'),
            'status' => 1
        ];
        if ($request->hasFile('image')) {
            $path = "/images/scratch_card_images/";
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
                $insert_arr['image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('image_ar')) {
            $path = "/images/scratch_card_images/";
            $check = $this->uploadFile($request, 'image_ar', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['image_ar'] = $returnArr->id;
            endif;
        }

        $add = ScratchCard::create($insert_arr);
        if ($add) {
            return back()->with('success', 'Scratch card added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding scratch card');
    }

    // Bulk upload to users
    protected function bulk_upload_to_users(Request $request)
    {
        return view('admin.scratch_cards.bulk_upload_to_users');
    }

    protected function bulk_upload_to_users_post (Request $request)
    {
        $file = file($request->file->getRealPath());
        $data = array_slice($file, 1);
        $parts = (array_chunk($data, 1000));

        if (count($parts)) {
            foreach ($parts as $part) {
                $data = array_map('str_getcsv', $part);
                // Itemcode upload
                UpdateUserScratchCard::dispatch($data);
            }
        }

        return back()->with('success', 'Process started');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function show($id)
    {
        $scratch_card = ScratchCard::find($id);
        $scratch_card_users = ScratchCardUser::where([
            'fk_scratch_card_id'=>$id,
            'deleted'=>0
        ])->orderBy('id','desc')->get();

        return view('admin.scratch_cards.show', [
            'scratch_card' => $scratch_card,
            'scratch_card_users' => $scratch_card_users
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $scratch_card = ScratchCard::find($id);

        $brands = Brand::where('deleted', '=', 0)
            ->orderBy('brand_name_en', 'asc')
            ->get();

        $category = Category::where('parent_id', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.scratch_cards.edit', [
            'scratch_card' => $scratch_card, 
            'brands' => $brands, 
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $scratch_card = ScratchCard::find($id);
        if (!$scratch_card) {
            return back()->withInput()->with('error', 'Scratch card not found!');
        }
        elseif ($request->input('apply_on')=='register' && $request->input('scratch_card_type')==0) {
            return back()->withInput()->with('error', 'On register, on spot reward / scratch card is not applicable!');
        }
        $update_arr = [
            'apply_on' => $request->input('apply_on'),
            'apply_on_min_amount' => $request->input('apply_on_min_amount') ?? 0.00,
            'scratch_card_type' => $request->input('scratch_card_type'),
            'type' => $request->input('type'),
            'min_amount' => $request->input('min_amount'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'scratch_card_code' => $request->input('scratch_card_code'),
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
            'discount' => $request->input('discount'),
            'expiry_in' => $request->input('expiry_in'),
            'uses_limit' => $request->input('uses_limit'),
            'status' => $scratch_card->status
        ];
        if ($request->hasFile('image')) {
            $path = "/images/scratch_card_images/";
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
                $update_arr['image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('image_ar')) {
            $path = "/images/scratch_card_images/";
            $check = $this->uploadFile($request, 'image_ar', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $update_arr['image_ar'] = $returnArr->id;
            endif;
        }

        $updated = $scratch_card->update($update_arr);
        if ($updated) {
            return back()->withInput()->with('success', 'Scratch card updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating scratch card');
    }
    
    public function change_scratch_cards_status(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('action');
        $update = ScratchCard::find($id)->update(['status' => $status]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating status']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $scratch_card = ScratchCard::find($id);
        if ($scratch_card) {
            $scratch_card->update(['deleted' => 1]);
            return redirect('admin/ads')->with('success', 'Scratch card deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting scratch card');
        }
    }
}
