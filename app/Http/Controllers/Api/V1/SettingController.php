<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Support\Str;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\Admin\UsefulLink;
use App\Models\UserNotification;
use App\Models\Admin\AppSettings;
use App\Models\Admin\SiteSections;
use App\Constants\SiteSectionConst;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Models\Admin\AppOnboardScreens;

class SettingController extends Controller
{
    /**
     * Method for language
     */
    public function languages(){
        try{
            $api_languages = get_api_languages();
        }catch(Exception $e) {
            return Response::error([$e->getMessage()],[],500);
        }
        return Response::success([__("Language data fetch successfully!")],[
            'languages' => $api_languages,
        ],200);
    }
    /**
     * Method for get the basic settings data 
     */
    public function basicSettings(){
        $basic_settings     = BasicSettings::orderBy("id")->get()->map(function($data){
            return [
                'id'                => $data->id,
                'site_name'         => $data->site_name,
                'base_color'        => $data->base_color,
                'site_logo_dark'    => $data->site_logo_dark,
                'site_logo'         => $data->site_logo,
                'site_fav_dark'     => $data->site_fav_dark,
                'site_fav'          => $data->site_fav,
                'created_at'        => $data->created_at,
            ];
        });
        $default_currency     = Currency::where('default',true)->orderBy("id")->get()->map(function($data){
            return [
                'id'                => $data->id,
                'country'           => $data->country,
                'name'              => $data->name,
                'code'              => $data->code,
                'symbol'            => $data->symbol,
            ];
        });
        //login section data
        $slug           = Str::slug(SiteSectionConst::LOGIN_SECTION);
        
        $login              = SiteSections::getData($slug)->get()->map(function($data){
            $app_local      = get_default_language_code();
            return [
                'image'     => $data->value->image,
                'title'  => $data->value->language->$app_local->title,
                'heading'  => $data->value->language->$app_local->heading,
            ];
        });

        //register section data
        $register_slug           = Str::slug(SiteSectionConst::REGISTER_SECTION);
        $register                = SiteSections::getData($register_slug)->get()->map(function($data){
            $app_local           = get_default_language_code();
            return [
                'image'     => $data->value->image,
                'title'  => $data->value->language->$app_local->title,
                'heading'  => $data->value->language->$app_local->heading,
            ];
        });
        

        // splash screen

        $splash_screen   = AppSettings::orderBy("id")->get()->map(function($data){

            return [
                'id'                          => $data->id,
                'version'                     => $data->version,
                'splash_screen_image'         => $data->splash_screen_image,
                'created_at'                  => $data->created_at,
            ];
        });

        // onboard screen
        $onboard_screen   = AppOnboardScreens::where('status',true)->orderBy("id")->get()->map(function($data){

            return [
                'id'                           => $data->id,
                'title'                        => $data->title,
                'sub_title'                    => $data->sub_title,
                'image'                        => $data->image,
                'status'                       => $data->status,
                'last_edit_by'                 => $data->last_edit_by,
                'created_at'                   => $data->created_at,

            ];
        });
        $privacy_policy = UsefulLink::where('type',global_const()::USEFUL_LINK_PRIVACY_POLICY)->first();
        $privacy_policy_link = route('link',$privacy_policy->slug);
        $about_page_link   = route('about');
        $contact_page_link   = route('contact');
        //basic image path
        $basic_image_path   = [
            'base_url'      => url('/'),
            'path_location' => files_asset_path_basename('image-assets'),
            'default_image' => files_asset_path_basename('default')
        ];
        //section image path
        $section_image_path   = [
            'base_url'      => url('/'),
            'path_location' => files_asset_path_basename('site-section'),
            'default_image' => files_asset_path_basename('default')
        ];

        //app image path
        $screen_image_path    = [
            'base_url'                     => url("/"),
            'path_location'                => files_asset_path_basename("app-images"),
            'default_image'                => files_asset_path_basename("default"),
        ];

        return Response::success([__('Basic Settings Data Fetch Successfully.')],[
            'default_currency'      => $default_currency,
            'basic_settings'        => $basic_settings,
            'login'                 => $login,
            'register'              => $register,
            'privacy_policy_link'   => $privacy_policy_link,
            'about_page_link'       => $about_page_link,
            'contact_page_link'     => $contact_page_link,
            'splash_screen'         => $splash_screen,
            'onboard_screen'        => $onboard_screen,
            'basic_image_path'      => $basic_image_path,
            'section_image_path'    => $section_image_path,
            'app_image_path'        => $screen_image_path,
        ],200);
    }
    /**
     * Method for get user notification
     */
    public function notification(){
        $user           = auth()->user()->id;
        $notification   = UserNotification::where('user_id',$user)->orderBy("id")->get()->map(function($data){
            return [
                'id'            => $data->id,
                'message'       => $data->message,
                'created_at'    => $data->created_at
            ]; 
        });
        return Response::success([__('Notification Data Fetch Successfuly.')],[
            'notification'      => $notification,
        ],200);
    }
    /**
     * Method for get all country list
     */
    public function countryList(){
        return Response::success([__('Country List Fetch Successfully!')],[
            'countries'     => get_all_countries(['id','name','mobile_code']),
        ],200);
    }
}
