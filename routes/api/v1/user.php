<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\Api\V1\User\MyBookingController;
use App\Http\Controllers\Api\V1\User\AuthorizationController;
use App\Http\Controllers\Api\V1\User\BuyCryptoController;
use App\Http\Controllers\Api\V1\User\ExchangeCryptoController;
use App\Http\Controllers\Api\V1\User\ParlourBookingController;
use App\Http\Controllers\Api\V1\User\SellCryptoController;
use App\Http\Controllers\Api\V1\User\TransactionLogController;
use App\Http\Controllers\Api\V1\User\WithdrawCryptoController;

Route::prefix("user")->name("api.user.")->group(function(){
    
    Route::middleware('auth:api')->group(function(){
        Route::post('google-2fa/otp/verify', [AuthorizationController::class,'verify2FACode']);
        
        Route::controller(ProfileController::class)->prefix('profile')->group(function(){
            Route::get('info','profileInfo');
            Route::post('info/update','profileInfoUpdate')->middleware('app.mode');
            Route::post('delete-account','deleteProfile')->middleware('app.mode');
            Route::post('password/update','profilePasswordUpdate')->middleware('app.mode');
            Route::get('/google-2fa', 'google2FA')->middleware('app.mode');
            Route::post('/google-2fa/status/update', 'google2FAStatusUpdate')->middleware('app.mode');
            Route::controller(AuthorizationController::class)->prefix('kyc')->group(function(){
                Route::get('input-fields','getKycInputFields');
                Route::post('submit','KycSubmit');
            });
        });
        // Logout Route
        Route::post('logout',[ProfileController::class,'logout']);
        Route::get('notification',[SettingController::class,'notification']);

        //transaction logs 
        Route::controller(TransactionLogController::class)->prefix('transaction')->group(function(){
            Route::get('buy-log','buyLog');
            Route::get('sell-log','sellLog');
            Route::get('withdraw-log','withdrawLog');
            Route::get('exchange-log','exchangeLog');
        });

        //buy crypto 
        Route::controller(BuyCryptoController::class)->prefix('buy-crypto')->name('buy.crypto.')->group(function(){
            Route::get('index','index');
            Route::post('store','store')->middleware(['kyc.verification.guard']);
            Route::post('submit','submit')->middleware(['kyc.verification.guard']);
            
             // POST Route For Unauthenticated Request
            Route::post('success/response/{gateway}', 'postSuccess')->name('payment.success')->withoutMiddleware(['auth:api','verification.guard','kyc.verification.guard','user.google.two.factor']);
            Route::post('cancel/response/{gateway}', 'postCancel')->name('payment.cancel')->withoutMiddleware(['auth:api','verification.guard','kyc.verification.guard','user.google.two.factor']);
        
            // Automatic Gateway Response Routes
            Route::get('success/response/{gateway}','success')->withoutMiddleware(['auth:api','verification.guard','kyc.verification.guard','user.google.two.factor'])->name("payment.success");
            Route::get("cancel/response/{gateway}",'cancel')->withoutMiddleware(['auth:api','verification.guard','kyc.verification.guard','user.google.two.factor'])->name("payment.cancel");

            //redirect with Btn Pay
            Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('payment.btn.pay')->withoutMiddleware(['auth:api','verification.guard','kyc.verification.guard','user.google.two.factor']);

            Route::get('manual/input-fields','manualInputFields'); 
            Route::post("manual/submit","manualSubmit");

            Route::get('payment-gateway/additional-fields','gatewayAdditionalFields');
            
            Route::prefix('payment')->name('payment.')->group(function() {
                Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm');
            });
        });
        
        //sell crypto 
        Route::controller(SellCryptoController::class)->prefix('sell-crypto')->group(function(){
            Route::get('index','index');
            Route::post('store','store')->middleware(['kyc.verification.guard']);
            Route::post('payment-info-store','paymentInfoStore')->middleware(['kyc.verification.guard']);
            Route::post('sell-payment-store','sellPaymentStore')->middleware(['kyc.verification.guard']);
            Route::post('confirm','confirm')->middleware(['kyc.verification.guard']);
        });

        //withdraw crypto 
        Route::controller(WithdrawCryptoController::class)->prefix('withdraw-crypto')->group(function(){
            Route::get('index','index');
            Route::get('check-wallet-address','checkWalletAddress');
            Route::post('store','store')->middleware(['kyc.verification.guard']);
            Route::post('confirm','confirm')->middleware(['kyc.verification.guard']);
        });
        //sell crypto 
        Route::controller(ExchangeCryptoController::class)->prefix('exchange-crypto')->group(function(){
            Route::get('index','index');
            Route::post('store','store')->middleware(['kyc.verification.guard']);
            Route::post('confirm','confirm')->middleware(['kyc.verification.guard']);
        }); 
    });  
});


