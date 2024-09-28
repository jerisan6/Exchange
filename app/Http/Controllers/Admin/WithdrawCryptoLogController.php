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
use App\Notifications\Admin\WithdrawCryptoMailNotification;
use App\Notifications\Admin\WithdrawCryptoRejectMailNotification;

class WithdrawCryptoLogController extends Controller
{
    /**
     * Method for withdraw crypto logs
     */
    public function index(){
        $page_title     = "All Withdraw Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)->orderBy('id','desc')->get();
        
        return view('admin.sections.crypto-logs.withdraw-crypto.all',compact(
            'page_title',
            'transactions'
        ));
    }
    /** 
    * Method for search withdraw crypto log  
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
        
        $transactions    = Transaction::auth()->where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)
                                    ->search($validated['text'])->get();
       
        return view('admin.components.search.withdraw-crypto-search',compact('transactions'));
        
    }
    /**
     * Method for exchange crypto details page
     * @param $id
     */
    public function details($id){
        $page_title         = "Withdraw Crypto Log Details";
        $transaction        = Transaction::with(['user','user_wallets'])->where('id',$id)->first();
        $transaction_device = TransactionDevice::where('transaction_id',$id)->first();
        
        if(!$transaction) return back()->with(['error' => ['Data not found']]);

        return view('admin.sections.crypto-logs.withdraw-crypto.details',compact(
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
        $transaction   = Transaction::with(['user','user_wallets'])->where('trx_id',$trx_id)->first();
        
        $form_data = [
            'data'        => $transaction,
            'status'      => $validated['status'],
        ];
        try{
            $transaction->update([
                'status' => $validated['status'],
            ]);
            if($basic_setting->email_notification == true){
                Notification::route("mail",$transaction->user->email)->notify(new WithdrawCryptoMailNotification($form_data));
            }
            
            UserNotification::create([
                'user_id'  => $transaction->user_id,
                'message'       => [
                    'title'     => "Withdraw Crypto",
                    'wallet'    => $transaction->details->data->sender_wallet->name,
                    'code'      => $transaction->details->data->sender_wallet->code,
                    'amount'    => $transaction->amount,
                    'status'    => $validated['status'],
                    'success'   => "Successfully Request Send."
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
        $transaction   = Transaction::with(['user','user_wallets'])->where('trx_id',$trx_id)->first();
        
        $form_data = [
            'data'        => $transaction,
            'status'      => "Reject",
        ];
        try{
            $transaction->update([
                'status'            => $validated['status'],
                'reject_reason'     => $validated['reject_reason']
            ]);
            if($validated['status'] == global_const()::STATUS_REJECT){
                $transaction->user_wallets->update([
                    'balance'   => $transaction->user_wallets->balance + $transaction->total_payable,
                ]);
            }
            if($basic_setting->email_notification == true){
                Notification::route("mail",$transaction->user->email)->notify(new WithdrawCryptoRejectMailNotification($form_data));
            }
            
            UserNotification::create([
                'user_id'  => $transaction->user_id,
                'message'       => [
                    'title'     => "Withdraw Crypto",
                    'wallet'    => $transaction->details->data->sender_wallet->name,
                    'code'      => $transaction->details->data->sender_wallet->code,
                    'amount'    => $transaction->amount,
                    'status'    => $validated['status'],
                    'success'   => "Successfully Request Send."
                ],
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Transaction Rejected successfully']]);
    }
    /**
     * Method for pending withdraw crypto logs
     */
    public function pending(){
        $page_title     = "Pending Withdraw Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_PENDING)->get();

        return view('admin.sections.crypto-logs.withdraw-crypto.pending',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for confirm withdraw crypto logs
     */
    public function confirm(){
        $page_title     = "Confirm Withdraw Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_CONFIRM_PAYMENT)->get();

        return view('admin.sections.crypto-logs.withdraw-crypto.confirm',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for rejected withdraw crypto logs
     */
    public function rejected(){
        $page_title     = "Rejected Withdraw Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)
                            ->orderBy('id','desc')->where('status',global_const()::STATUS_REJECT)->get();

        return view('admin.sections.crypto-logs.withdraw-crypto.reject',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for canceled withdraw crypto logs
     */
    public function canceled(){
        $page_title     = "Canceled Withdraw Crypto Logs";
        $transactions   = Transaction::where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)->orderBy('id','desc')->where('status',global_const()::STATUS_CANCEL)->get();

        return view('admin.sections.crypto-logs.withdraw-crypto.cancel',compact(
            'page_title',
            'transactions'
        ));
    }
}
