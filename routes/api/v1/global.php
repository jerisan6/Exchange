<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SettingController;

Route::controller(SettingController::class)->group(function(){
    Route::get("basic-settings","basicSettings");
    Route::get("country-list","countryList");
    Route::get("language","languages");
});
