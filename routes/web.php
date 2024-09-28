<?php

use App\Http\Controllers\Frontend\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller(SiteController::class)->middleware(['active.page'])->group(function(){
    Route::get('/','index')->name('index');
    Route::get('about','about')->name('about');
    Route::get('service','service')->name('service');
    Route::get('journal','journal')->name('journal');
    Route::get('all-journals','journals')->name('journals');
    Route::get('journal-detais/{slug}','journalDetails')->name('journal.details');
    Route::get('journal-category/{slug}','journalCategory')->name('journal.category');
    Route::get('contact','contact')->name('contact');
    Route::post("subscribe",'subscribe')->name("subscribe");
    Route::get('link/{slug}','link')->name('link');
    Route::post("contact-request",'contactRequest')->name("contact.request");
});
