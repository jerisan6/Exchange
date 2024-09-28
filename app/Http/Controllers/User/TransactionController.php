<?php

namespace App\Http\Controllers\User;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Method for view buy log page
     * @return view
     */
    public function buyLog(){
        $page_title  = "- Buy Logs";
        $transactions = Transaction::auth()->where("type",PaymentGatewayConst::BUY_CRYPTO)->orderBy('id','desc')->get();
        
        return view('user.sections.transaction-logs.buy-log',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for view sell log page
     * @return view
     */
    public function sellLog(){
        $page_title     = "- Sell Logs";
        $transactions   = Transaction::auth()->where("type",PaymentGatewayConst::SELL_CRYPTO)->orderBy('id','desc')->get();
        

        return view('user.sections.transaction-logs.sell-log',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for view withdraw log page
     * @return view
     */
    public function withdrawLog(){
        $page_title     = "- Withdraw Logs";
        $transactions   = Transaction::auth()->where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)->orderBy('id','desc')->get();

        return view('user.sections.transaction-logs.withdraw-log',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for view exchange log page
     * @return view
     */
    public function exchangeLog(){
        $page_title     = "- Exchange Logs";
        $transactions   = Transaction::auth()->where('type',PaymentGatewayConst::EXCHANGE_CRYPTO)->orderBy('id','desc')->get();

        return view('user.sections.transaction-logs.exchange-log',compact(
            'page_title',
            'transactions',
        ));
    }
    /** 
    * Method for search buy crypto log  
    */
    public function buyLogSearch(Request $request){
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);
        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        
        $transactions    = Transaction::auth()->where('type',PaymentGatewayConst::BUY_CRYPTO)
                                    ->search($validated['text'])->get();
       
        return view('user.components.search-logs.buy-log',compact('transactions'));

    }
    /** 
    * Method for search sell crypto log  
    */
    public function sellLogSearch(Request $request){
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);
        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        
        $transactions    = Transaction::auth()->where('type',PaymentGatewayConst::SELL_CRYPTO)
                                    ->search($validated['text'])->get();
       
        return view('user.components.search-logs.sell-log',compact('transactions'));

    }
    /** 
    * Method for search withdraw crypto log  
    */
    public function withdrawLogSearch(Request $request){
       
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);
        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        
        $transactions    = Transaction::auth()->where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)
                                    ->search($validated['text'])->get();
       
        return view('user.components.search-logs.withdraw-log',compact('transactions'));

    }
    /** 
    * Method for search exchange crypto log  
    */
    public function exchangeLogSearch(Request $request){
       
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);
        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        
        $transactions    = Transaction::auth()->where('type',PaymentGatewayConst::EXCHANGE_CRYPTO)
                                    ->search($validated['text'])->get();
       
        return view('user.components.search-logs.exchange-log',compact('transactions'));

    }
    /**
     * Method for download the file
     * @param $file
     */
    public function download($file){
        if ($file) {
            $files = get_files_path('kyc-files') . '/' . $file;
            if (file_exists($files)) {
                return response()->download($files, $file);
            } else {
                return "File not found in storage: " . $files;
            }
        }
    }
}
