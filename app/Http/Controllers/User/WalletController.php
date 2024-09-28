<?php

namespace App\Http\Controllers\User;

use App\Models\UserWallet;
use App\Models\Admin\Network;
use App\Http\Controllers\Controller;
use App\Models\Admin\CurrencyHasNetwork;

class WalletController extends Controller
{
    /**
     * Method for view wallet page
     * @return view
     */
    public function index(){
        $page_title     = "- All Wallet";
        $wallets        = UserWallet::auth()->with(['currency'])->orderByDesc('id')->get();
        
        return view('user.sections.wallet.index',compact(
            'page_title',
            'wallets'
        ));
    }
    /**
     * Method for wallet details
     * @param $public_address
     */
    public function walletDetails($public_address){
        $page_title     = "- Wallet Details";
        $wallet         = UserWallet::auth()->with(['currency'])->where('public_address',$public_address)->first();
        if(!$wallet) return back()->with(['error' => ['Wallet not found!']]);
        $qr_code        = generateQr($wallet->public_address);
       
        $get_total_networks     = CurrencyHasNetwork::where('currency_id',$wallet->currency_id)->pluck('network_id');
        $network_names          = Network::whereIn('id',$get_total_networks)->pluck('name');
        
        return view('user.sections.wallet.details',compact(
                'page_title',
                'wallet',
                'qr_code',
                'network_names'
        ));
    }
}
