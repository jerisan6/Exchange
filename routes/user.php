<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SecurityController;
use App\Http\Controllers\User\BuyCryptoController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\SellCryptoController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\AuthorizationController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\ExchangeCryptoController;
use App\Http\Controllers\User\WithdrawCryptoController;

Route::prefix("user")->name("user.")->group(function(){
    Route::controller(DashboardController::class)->group(function(){
        Route::get('dashboard','index')->name('dashboard');
        Route::post('logout','logout')->name('logout');
        Route::delete('delete/account','deleteAccount')->name('delete.account')->middleware(['app.mode']);
    });

    Route::controller(ProfileController::class)->prefix("profile")->name("profile.")->group(function(){
        Route::get('/','index')->name('index');
        Route::put('password/update','passwordUpdate')->name('password.update')->middleware(['app.mode']);
        Route::put('update','update')->name('update')->middleware(['app.mode']);
    });

    // wallet 
    Route::controller(WalletController::class)->prefix('wallet')->name('wallet.')->group(function(){
        Route::get('/','index')->name('index');
        Route::get('wallet-details/{public_address}','walletDetails')->name('details');
        
    });

    //buy crypto
    Route::controller(BuyCryptoController::class)->prefix('buy-crypto')->middleware(['kyc.verification.guard'])->name('buy.crypto.')->group(function(){
        Route::get('/','index')->name('index');
        Route::post('get/currency/networks','getCurrencyNetworks')->name('get.currency.networks');
        Route::post('store','store')->name('store');
        Route::get('preview/{identifier}','preview')->name('preview');
        Route::post('submit','submit')->name('submit');

        //manual
        Route::get('manual/{token}','showManualForm')->name('manual.form');
        Route::post('manual/submit/{token}','manualSubmit')->name('manual.submit');

        Route::prefix('payment')->name('payment.')->group(function() {
            Route::get('crypto/address/{trx_id}','cryptoPaymentAddress')->name('crypto.address');
            Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm');
        });

        // POST Route For Unauthenticated Request ssl commerz
        Route::post('success/response/{gateway}', 'postSuccess')->name('payment.success')->withoutMiddleware(['auth','verification.guard','kyc.verification.guard','user.google.two.factor']);
        Route::post('cancel/response/{gateway}', 'postCancel')->name('payment.cancel')->withoutMiddleware(['auth','verification.guard','kyc.verification.guard','user.google.two.factor']);

        //redirect with Btn Pay
        Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('payment.btn.pay')->withoutMiddleware(['auth','verification.guard','kyc.verification.guard','user.google.two.factor']);

        // redirect with HTML form route 
        Route::get('redirect/form/{gateway}', 'redirectUsingHTMLForm')->name('payment.redirect.form')->withoutMiddleware(['auth','verification.guard','kyc.verification.guard','user.google.two.factor']);

        Route::get('success/response/{gateway}','success')->name('payment.success');
        Route::get('success/{gateway}','successPagadito')->name('payment.success.pagadito')->withoutMiddleware(['auth','verification.guard','kyc.verification.guard','user.google.two.factor']);
        Route::get("cancel/response/{gateway}",'cancel')->name('payment.cancel');
        Route::post("callback/response/{gateway}",'callback')->name('payment.callback')->withoutMiddleware(['web','auth','verification.guard','kyc.verification.guard','user.google.two.factor']);
    });

    //sell crypto
    Route::controller(SellCryptoController::class)->prefix('sell-crypto')->middleware(['kyc.verification.guard'])->name('sell.crypto.')->group(function(){
        Route::get('/','index')->name('index');
        Route::post('get/currency/networks','getCurrencyNetworks')->name('get.currency.networks');
        Route::post('store','store')->name('store');
        Route::get('sell-payment/{identifier}','sellPayment')->name('sell.payment');
        Route::post('sell-payment-store/{identifier}','sellPaymentStore')->name('sell.payment.store');
        Route::get('payment-info/{identifier}','paymentInfo')->name('payment.info');
        Route::post('payment-info-store/{identifier}','paymentInfoStore')->name('payment.info.store');
        Route::get('preview/{identifier}','preview')->name('preview');
        Route::post('confirm/{identifier}','confirm')->name('confirm');
    });

    //withdraw crypto
    Route::controller(WithdrawCryptoController::class)->prefix('withdraw-crypto')->middleware(['kyc.verification.guard'])->name('withdraw.crypto.')->group(function(){
        Route::get('/','index')->name('index');
        Route::post('check/wallet/address','checkWalletAddress')->name('check.address.exist');
        Route::post('store','store')->name('store');
        Route::get('preview/{identifier}','preview')->name('preview');
        Route::post('confirm/{identifier}','confirm')->name('confirm');
    });

    //exchange crypto
    Route::controller(ExchangeCryptoController::class)->prefix('exchange-crypto')->middleware(['kyc.verification.guard'])->name('exchange.crypto.')->group(function(){
        Route::get('/','index')->name('index');
        Route::post('store','store')->name('store');
        Route::get('preview/{identifier}','preview')->name('preview');
        Route::post('confirm/{identifier}','confirm')->name('confirm');
    });

    //buy log
    Route::controller(TransactionController::class)->prefix('transaction')->name('transaction.')->group(function(){
        Route::get('buy-log','buyLog')->name('buy.log');
        Route::get('sell-log','sellLog')->name('sell.log');
        Route::get('withdraw-log','withdrawLog')->name('withdraw.log');
        Route::get('exchange-log','exchangeLog')->name('exchange.log');
        Route::get('file-download/{file}','download')->name('file.download');

        //search transaction logs
        Route::controller(TransactionController::class)->prefix('search')->name('search.')->group(function(){
            Route::post('buy-log','buyLogSearch')->name('buy.log');
            Route::post('sell-log','sellLogSearch')->name('sell.log');
            Route::post('withdraw-log','withdrawLogSearch')->name('withdraw.log');
            Route::post('exchange-log','exchangeLogSearch')->name('exchange.log');
        });
    });

    //security
    Route::controller(SecurityController::class)->prefix('security')->name('security.')->group(function(){
        Route::get('google/2fa','google2FA')->name('google.2fa')->middleware('app.mode');
        Route::post('google/2fa/status/update','google2FAStatusUpdate')->name('google.2fa.status.update')->middleware('app.mode');
    });

    //support ticket
    Route::controller(SupportTicketController::class)->prefix("prefix")->name("support.ticket.")->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('conversation/{encrypt_id}','conversation')->name('conversation');
        Route::post('message/send','messageSend')->name('message.send');
    });

    //kyc verification
    Route::controller(AuthorizationController::class)->prefix("authorize")->name('authorize.')->group(function(){
        Route::get('kyc','showKycFrom')->name('kyc');
        Route::post('kyc/submit','kycSubmit')->name('kyc.submit');
    });

});
