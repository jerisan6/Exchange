<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\UserNotification;
use App\Models\TransactionDevice;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Admin\SellCryptoMailNotification;
use App\Notifications\Admin\SellCryptoRejectMailNotification;

class SellCryptoLogController extends Controller
{
    /**
     * Method for sell crypto logs
     */
    public function index(){
        $page_title     = "All Sell Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::SELL_CRYPTO)->orderBy('id','desc')->get();

        return view('admin.sections.crypto-logs.sell-crypto.all',compact(
            'page_title',
            'transactions'
        ));
    }
    /** 
    * Method for search sell crypto log  
    */
    public function search(Request $request) {
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
       
        return view('admin.components.search.sell-crypto-search',compact('transactions'));
        
    }
    /**
     * Method for sell crypto details page
     * @param $id
     */
    public function details($id){
        $page_title     = "Sell Crypto Log Details";
        $transaction    = Transaction::with(['user','user_wallets','currency'])->where('id',$id)->first();
        $transaction_device = TransactionDevice::where('transaction_id',$id)->first();
        if(!$transaction) return back()->with(['error' => ['Data not found']]);

        return view('admin.sections.crypto-logs.sell-crypto.details',compact(
            'page_title',
            'transaction',
            'transaction_device'
        ));

    }
    /**
     * Method for update status 
     * @param $trx_id
     * @param Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request,$trx_id){
        
        $basic_setting  = BasicSettings::first();
        $validator      = Validator::make($request->all(),[
            'status'            => 'required|integer',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated      = $validator->validate();
        $transaction    = Transaction::with(['user','user_wallets','currency'])->where('trx_id',$trx_id)->first();
        
        
        $form_data = [
            'data'        => $transaction,
            'status'      => "Confirm",
        ];
        try{
            $transaction->update([
                'status' => $validated['status'],
            ]);
            
            if($basic_setting->email_notification == true){
                Notification::route("mail",$transaction->user->email)->notify(new SellCryptoMailNotification($form_data));
            }
            
            UserNotification::create([
                'user_id'  => $transaction->user_id,
                'message'       => [
                    'title'     => "Sell Crypto",
                    'payment'   => $transaction->details->data->payment_method->name,
                    'wallet'    => $transaction->details->data->sender_wallet->name,
                    'code'      => $transaction->details->data->sender_wallet->code,
                    'amount'    => $transaction->amount,
                    'status'    => $validated['status'],
                    'success'   => "Successfully Added."
                ],
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Transaction Status updated successfully']]);
    }
    /**
     * Method for reject transaction
     * @param $trx_id
     * @param \Illuminate\Http\Request $request
     */
    public function reject(Request $request,$trx_id){
        $basic_setting  = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'reject_reason'     => 'required',
            'status'            => 'required|integer',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();
        $transaction   = Transaction::with(['user','user_wallets','currency'])->where('trx_id',$trx_id)->first();
        $form_data = [
            'data'        => $transaction,
            'status'      => "Reject",
        ];
        try{
            $transaction->update([
                'status'            => $validated['status'],
                'reject_reason'     => $validated['reject_reason']
            ]);
            if($validated['status'] == global_const()::STATUS_REJECT && $transaction->details->data->sender_wallet->type == global_const()::INSIDE_WALLET){
                $transaction->user_wallets->update([
                    'balance'   => $transaction->user_wallets->balance + $transaction->total_payable,
                ]);
            }
            if($basic_setting->email_notification == true){
                Notification::route("mail",$transaction->user->email)->notify(new SellCryptoRejectMailNotification($form_data));
            }
            
            UserNotification::create([
                'user_id'  => $transaction->user_id,
                'message'       => [
                    'title'     => "Sell Crypto",
                    'payment'   => $transaction->details->data->payment_method->name,
                    'wallet'    => $transaction->details->data->sender_wallet->name,
                    'code'      => $transaction->details->data->sender_wallet->code,
                    'amount'    => $transaction->amount,
                    'status'    => $validated['status'],
                    'success'   => "Successfully Added."
                ],
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Transaction Rejected successfully']]);
    }
    /**
     * Method for pending sell crypto logs
     */
    public function pending(){
        $page_title     = "Pending Sell Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::SELL_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_PENDING)->get();

        return view('admin.sections.crypto-logs.sell-crypto.pending',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for confirm sell crypto logs
     */
    public function confirm(){
        $page_title     = "Confirm Sell Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::SELL_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_CONFIRM_PAYMENT)->get();

        return view('admin.sections.crypto-logs.sell-crypto.confirm',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for rejected sell crypto logs
     */
    public function rejected(){
        $page_title     = "Rejected Sell Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::SELL_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_REJECT)->get();

        return view('admin.sections.crypto-logs.sell-crypto.reject',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for canceled sell crypto logs
     */
    public function canceled(){
        $page_title     = "Canceled Sell Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::SELL_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_CANCEL)->get();

        return view('admin.sections.crypto-logs.sell-crypto.cancel',compact(
            'page_title',
            'transactions'
        ));
    }
}
