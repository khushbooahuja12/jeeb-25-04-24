<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\CoreApiController;
use App\Model\UserWallet;
use App\Model\UserWalletPayment;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WalletController extends CoreApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function index(Request $request)
    {
        if ($_GET) {
            $wallets = UserWallet::join('users', 'users.id', '=', 'user_wallet.fk_user_id')
                ->select("users.*", 'user_wallet.total_points')
                ->orderBy('users.id', 'desc');

            if (isset($_GET['mobile']) && $request->query('mobile') != '') {
                $wallets = $wallets->where('users.mobile', 'LIKE', '%'.$_GET['mobile'].'%');
            }

            $wallets = $wallets->paginate(50);
        } else {
            $wallets = UserWallet::join('users', 'users.id', '=', 'user_wallet.fk_user_id')
            ->select("users.*", 'user_wallet.total_points')
            ->orderBy('users.id', 'desc')
            ->paginate(50);
        }

        return view('account.wallet.index', [
            'wallets' => $wallets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = UserWallet::join('users', 'users.id', '=', 'user_wallet.fk_user_id')
            ->select("users.*", 'user_wallet.total_points')
            ->orderBy('users.id', 'desc')
            ->where('users.id', '=', $id)
            ->first();
        $wallets = UserWalletPayment::where('fk_user_id','=',$id)
            ->orderBy('id', 'desc')
            ->paginate(50);
        return view('account.wallet.show', [
            'user' => $user,
            'wallets' => $wallets
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
