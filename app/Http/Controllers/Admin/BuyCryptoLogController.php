<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\UserNotification;
use App\Models\TransactionDevice;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\BasicSettings;
use App\Notifications\Admin\BuyCryptoMailNotification;
use App\Notifications\Admin\CryptoRejectMailNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class BuyCryptoLogController extends Controller
{
    /**
     * Method for buy crypto logs
     */
    public function index(){
        $page_title     = "All Buy Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->orderBy('id','desc')->get();

        return view('admin.sections.crypto-logs.buy-crypto.all',compact(
            'page_title',
            'transactions'
        ));
    }
    /** 
    * Method for search buy crypto log  
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
        
        $transactions    = Transaction::auth()->where('type',PaymentGatewayConst::BUY_CRYPTO)
                                    ->search($validated['text'])->get();
       
        return view('admin.components.search.buy-crypto-search',compact('transactions'));
        
    }
    /**
     * Method for buy crypto details page
     * @param $id
     */
    public function details($id){
        $page_title         = "Buy Crypto Log Details";
        $transaction        = Transaction::with(['user','user_wallets','currency'])->where('id',$id)->first();
        $transaction_device = TransactionDevice::where('transaction_id',$id)->first();
        if(!$transaction) return back()->with(['error' => ['Data not found']]);

        return view('admin.sections.crypto-logs.buy-crypto.details',compact(
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
        $validator = Validator::make($request->all(),[
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
            'status'      => $validated['status'],
        ];
        try{
            $transaction->update([
                'status' => $validated['status'],
            ]);
            if($validated['status'] == global_const()::STATUS_CONFIRM_PAYMENT && $transaction->currency->gateway->isManual()){
                $transaction->user_wallets->update([
                    'balance'   => $transaction->user_wallets->balance + $transaction->amount,
                ]);
            }
            if($basic_setting->email_notification == true){
                Notification::route("mail",$transaction->user->email)->notify(new BuyCryptoMailNotification($form_data));
            }
            
            UserNotification::create([
                'user_id'  => $transaction->user_id,
                'message'       => [
                    'title'     => "Buy Crypto",
                    'payment'   => $transaction->details->data->payment_method->name,
                    'wallet'    => $transaction->details->data->wallet->name,
                    'code'      => $transaction->details->data->wallet->code,
                    'amount'    => $transaction->details->data->amount,
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
            
            if($basic_setting->email_notification == true){
                Notification::route("mail",$transaction->user->email)->notify(new CryptoRejectMailNotification($form_data));
            }
            
            UserNotification::create([
                'user_id'  => $transaction->user_id,
                'message'       => [
                    'title'     => "Buy Crypto",
                    'payment'   => $transaction->details->data->payment_method->name,
                    'wallet'    => $transaction->details->data->wallet->name,
                    'code'      => $transaction->details->data->wallet->code,
                    'amount'    => $transaction->details->data->amount,
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
     * Method for pending buy crypto logs
     */
    public function pending(){
        $page_title     = "Pending Buy Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_PENDING)->get();

        return view('admin.sections.crypto-logs.buy-crypto.pending',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for confirm buy crypto logs
     */
    public function confirm(){
        $page_title     = "Confirm Buy Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_CONFIRM_PAYMENT)->get();

        return view('admin.sections.crypto-logs.buy-crypto.confirm',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for rejected buy crypto logs
     */
    public function rejected(){
        $page_title     = "Rejected Buy Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_REJECT)->get();

        return view('admin.sections.crypto-logs.buy-crypto.reject',compact(
            'page_title',
            'transactions'
        ));
    }
    
}
