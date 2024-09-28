<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Admin\Blog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\Admin\Language;
use App\Constants\LanguageConst;
use App\Models\Admin\BlogCategory;
use App\Models\Admin\SiteSections;
use App\Constants\SiteSectionConst;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class SetupSectionsController extends Controller
{
    protected $languages;

    public function __construct()
    {
        $this->languages = Language::get();
    }

    /**
     * Register Sections with their slug
     * @param string $slug
     * @param string $type
     * @return string
     */
    public function section($slug,$type) {
        $sections = [
            'banner'            => [
                'view'          => "bannerView",
                'update'        => "bannerUpdate",
            ],
            'security'          => [
                'view'          => "securityView",
                'update'        => "securityUpdate",
                'itemStore'     => "securityItemStore",
                'itemUpdate'    => "securityItemUpdate",
                'itemDelete'    => "securityItemDelete",
            ],
            'how-it-work'       => [
                'view'          => "howItsWorkView",
                'update'        => "howItsWorkUpdate",
                'itemStore'     => "howItsWorkItemStore",
                'itemUpdate'    => "howItsWorkItemUpdate",
                'itemDelete'    => "howItsWorkItemDelete"
            ],
            'download-app'      => [
                'view'          => "downloadAppView",
                'update'        => "downloadAppUpdate",
                'itemStore'     => "downloadAppItemStore",
                'itemUpdate'    => "downloadAppItemUpdate",
                'itemDelete'    => "downloadAppItemDelete"
            ],
            'statistic'         => [
                'view'          => "statisticView",
                'update'        => "statisticUpdate",
                'itemStore'     => "statisticItemStore",
                'itemUpdate'    => "statisticItemUpdate",
                'itemDelete'    => "statisticItemDelete"
            ],
            'call-to-action'    => [
                'view'          => "callToActionView",
                'update'        => "callToActionUpdate",
            ],
            'footer'            => [
                'view'          => "footerView",
                'update'        => "footerUpdate"
            ],
            'news-letter'       =>[
                'view'          => "newsLetterView",
                'update'        => "newsLetterUpdate",    
            ],
            'about'             => [
                'view'          => "aboutView",
                'update'        => "aboutUpdate",
            ],
            'faq'               => [
                'view'          => "faqView",
                'update'        => "faqUpdate",
                'itemStore'     => "faqItemStore",
                'itemUpdate'    => "faqItemUpdate",
                'itemDelete'    => "faqItemDelete",
            ],
            'service'           => [
                'view'          => "serviceView",
                'update'        => "serviceUpdate",
                'itemStore'     => "serviceItemStore",
                'itemUpdate'    => "serviceItemUpdate",
                'itemDelete'    => "serviceItemDelete",
            ],
            'blog'              => [
                'view'          => "blogView",
                'update'        => "blogUpdate",
            ],
            'contact'           => [
                'view'          => "contactView",
                'update'        => "contactUpdate",
            ], 
            'login'             => [
                'view'          => "loginView",
                'update'        => "loginUpdate",
            ],
            'register'          => [
                'view'          => "registerView",
                'update'        => "registerUpdate",
            ],
        ];

        if(!array_key_exists($slug,$sections)) abort(404);
        if(!isset($sections[$slug][$type])) abort(404);
        $next_step = $sections[$slug][$type];
        return $next_step;
    }

    /**
     * Method for getting specific step based on incomming request
     * @param string $slug
     * @return method
     */
    public function sectionView($slug) {
        $section = $this->section($slug,'view');
        return $this->$section($slug);
    }

    /**
     * Method for distribute store method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionItemStore(Request $request, $slug) {
        $section = $this->section($slug,'itemStore');
        return $this->$section($request,$slug);
    }

    /**
     * Method for distribute update method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionItemUpdate(Request $request, $slug) {
        $section = $this->section($slug,'itemUpdate');
        return $this->$section($request,$slug);
    }

    /**
     * Method for distribute delete method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionItemDelete(Request $request,$slug) {
        $section = $this->section($slug,'itemDelete');
        return $this->$section($request,$slug);
    }

    /**
     * Method for distribute update method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionUpdate(Request $request,$slug) {
        $section = $this->section($slug,'update');
        return $this->$section($request,$slug);
    }

    /**
     * Mehtod for show banner section page
     * @param string $slug
     * @return view
     */
    public function bannerView($slug) {
        $page_title = "Banner Section";
        $section_slug = Str::slug(SiteSectionConst::BANNER_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.banner-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }

    /**
     * Mehtod for update banner section information
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function bannerUpdate(Request $request,$slug) {
        $basic_field_name   = [
            'title'         => "required|string|max:100",
            'heading'       => "required|string|max:100",
            'sub_heading'   => "required|string|max:255",
            'button_name'   => "required|string|max:50",
        ];

        $slug = Str::slug(SiteSectionConst::BANNER_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }

        $validator  = Validator::make($request->all(),[
            'image'             => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
            'button_image'      => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
            'button_link'       => "required|string|url|max:255",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();
        $validated = $validator->validate();

        $data['image']          = $section->value->image ?? "";
        $data['button_image']   = $section->value->button_image ?? "";

        $data['button_link']    = $validated['button_link'];

        if($request->hasFile("image")) {
            $data['image']              = $this->imageValidate($request,"image",$section->value->image ?? null);
        }
        if($request->hasFile("button_image")){
            $data['button_image']       = $this->imageValidate($request,"button_image",$section->value->button_image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;
        
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }

        return back()->with(['success' => ['Section updated successfully!']]);
    }
    /**
     * Method for distribute update method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function securityView($slug){
        $page_title     = "Security Section";
        $section_slug   = Str::slug(SiteSectionConst::SECURITY_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.security-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Mehtod for update security section information
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
    */
    public function securityUpdate(Request $request,$slug) {
        
        $basic_field_name   = [
            'title'         => 'required|string|max:100',
            'heading'       => 'required|string|max:100',
        ];

        $slug           = Str::slug(SiteSectionConst::SECURITY_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }

        $data['language']      = $this->contentValidate($request,$basic_field_name);
        $update_data['key']    = $slug;
        $update_data['value']  = $data;
        
        try{
            SiteSections::updateOrCreate(['key'=>$slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error'=>'Something went wrong! Please try again.']);
        }
        return back()->with(['success'  =>  ['Section updated successfully!']]);

    }
    /**
     * Mehtod for store security item information
     * @param string $slug
     * @param \Illuminate\Http\Request $request
    */
    public function securityItemStore(Request $request,$slug) {
        $basic_field_name  = [
            'item_title'     => "required|string|max:255",
            'item_heading'       => "required|string|max:500",
            
        ];

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"security-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug = Str::slug(SiteSectionConst::SECURITY_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $validator  = Validator::make($request->all(),[
            'icon'              => "required|string",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','security-add');
        $validated = $validator->validate();
        $unique_id = uniqid();
        
        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['status']   = 1;
        $section_data['items'][$unique_id]['id']       = $unique_id;
        $section_data['items'][$unique_id]['icon']              = $validated['icon'];
        $update_data['key']     = $slug;
        $update_data['value']   = $section_data;
       
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return back()->with(['success'   => ['Section item added successfully!']]);
    }
    /**
     * Mehtod for update security item information
     * @param string $slug
     * @param \Illuminate\Http\Request $request
    */
    public function securityItemUpdate(Request $request,$slug) {
        $request->validate([
            'target'         =>'required|string',
        ]);

        $basic_field_name = [
            'item_title_edit'  => "required|string|max:255",
            'item_heading_edit'    => "required|string|max:500",
        ];

        $slug              = Str::slug(SiteSectionConst::SECURITY_SECTION);
        $section           = SiteSections::getData($slug)->first();

        if(!$section) return back()->with(['error' => ['Section Not Found!']]);
        $section_values    = json_decode(json_encode($section->value),true);
        
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section Item Not Found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['[error' => ['Section Item is invalid']]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"security-edit");
        
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $language_wise_data = array_map(function($language){
            return replace_array_key($language,'_edit');
        },$language_wise_data);
        $validator  = Validator::make($request->all(),[
            'icon_edit'              => "required|string",
        ]);
        $validated = $validator->validate();
        $section_values['items'][$request->target]['language'] = $language_wise_data;
        $section_values['items'][$request->target]['icon'] = $validated['icon_edit'];

        try{
            $section->update([
                'value'  =>$section_values,
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success'   => ['Information updated successfully!']]);    
    }
    /**
     * Mehtod for delete security item information
     * @param string $slug
     * @return view
     */
    public function securityItemDelete(request $request,$slug){
        $request->validate([
            'target'    =>'required|string',
        ]);

        $slug           = Str::slug(SiteSectionConst::SECURITY_SECTION);
        $section        = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);

        if(!isset($section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e){
            return $e->getMessage();
        }
        return back()->with(['success' => ['Section item deleted successfully!']]);
    }
    /**
     * Mehtod for update security item status 
     * @param string $slug
     * @return view
     */
    public function securityStatusUpdate(Request $request,$slug) {
        
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        
        if ($validator->stopOnFirstFailure()->fails()) {
            return Response::error($validator->errors()->all(),null,400);
        }

        $slug           = Str::slug(SiteSectionConst::SECURITY_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }
        if(array_key_exists("items",$data) && array_key_exists($request->data_target,$data['items'])) {
            $data['items'][$request->data_target]['status'] = ($request->status == 1) ? 0 : 1;
        }else {
            return Response::error(['Items not found or invalid!'],[],404);
        }

        $section->update([
            'value'     => $data,
        ]);

        return Response::success(['Section item status updated successfully!'],[],200);
        
    }
    /**
     * Method for show how its work section page
     * @param string $slug
     * @return view
     */
    public function howItsWorkView($slug){
        $page_title     = "How Its Work Section";
        $section_slug   = Str::slug(SiteSectionConst::HOW_ITS_WORK_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.how-its-work-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    /**
     * Method for update howItsWork section page
     * @param string $slug
     * @return view
     */
    public function howItsWorkUpdate(Request $request,$slug){

        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string|max:100',
            'sub_heading' => 'required|string',
        ];

        $slug     = Str::slug(SiteSectionConst::HOW_ITS_WORK_SECTION);
        $section  = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data    = json_decode(json_encode($section->value),true);
        }else{
            $data    =[];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();
       
        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);

        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        
        
        try{
            SiteSections::updateOrCreate(["key"=>$slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Section updated successfully!']]);
     
    }
    /**
     * Method for store howItsWork item
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function howItsWorkItemStore(Request $request,$slug) {
        $basic_field_name = [
            'item_title'       => 'required|string|max:100',
            'item_heading'       => 'required|string',
        ];


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"HowItsWork-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        
        $slug       = Str::slug(SiteSectionConst::HOW_ITS_WORK_SECTION);
        $section    = SiteSections::where('key',$slug)->first();
        if($section != null){
            $section_data = json_decode(json_encode($section->value),true);
        }else{
            $section_data = [];
        }
        $unique_id =uniqid();

        $validator  = Validator::make($request->all(),[
            'icon'            => "required|string|max:100",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','HowItsWork-add');
        $validated = $validator->validate();

        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['id']       = $unique_id;
        $section_data['items'][$unique_id]['icon']     = $validated['icon'];

        $update_data['key']   = $slug;
        $update_data['value'] = $section_data;
        
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }
        return back()->with(['success' => ['Section item added successfully!']]);
    }
    
    /**
     * Method for update howItsWork item
     * @param string $slug
     * @return view
     */
    public function howItsWorkItemUpdate(Request $request,$slug){
        $request->validate([
            'target'           => 'required|string',
        ]);

        $basic_field_name      = [
            "item_title_edit"  => "required|string|max:100",
            "item_heading_edit"  => "required|string",
        ];
        
        $slug        = Str::slug(SiteSectionConst::HOW_ITS_WORK_SECTION);
        $section     = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values["items"])) return back()->with(['error' => ['Section item not found']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid!']]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"HowItsWork-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
         
        $language_wise_data = array_map(function($language){
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $validator  = Validator::make($request->all(),[
            'icon_edit'            => "required|string|max:100",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','HowItsWork-edit');
        $validated = $validator->validate();

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        $section_values['items'][$request->target]['icon']     = $validated['icon_edit'];
        
       
       try{
            $section->update([
                'value' => $section_values,
            ]);
       }catch(Exception $e){
            return back()->with(['error' => ['Something Went wrong! Please try again.']]);
       }
       return back()->with(['success' => ['Section item updated successfully!']]);
    }
    /**
     * Method for delete howItsWork item
     * @param string $slug
     * @return view
     */
    public function howItsWorkItemDelete(Request $request,$slug){
        $request->validate([
            'target' => 'required|string',
        ]);

        $slug     = Str::slug(SiteSectionConst::HOW_ITS_WORK_SECTION);
        $section  = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values  = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section Item not Found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid']]);

        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Section item deleted successfully!']]);    
    }
    /**
     * Method for show download app section
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     */
    public function downloadAppView($slug){
        $page_title     = "Download App Section";
        $section_slug   = Str::slug(SiteSectionConst::DOWNLOAD_APP_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.download-app-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Method for update download app section
     * @param string
     * @param \Illuminate\\Http\Request $request
     */
    
    public function downloadAppUpdate(Request $request,$slug){
        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string|max:100',
            'sub_heading' => 'required|string',
        ];

        $slug             = Str::slug(SiteSectionConst::DOWNLOAD_APP_SECTION);
        $section          = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();
       
        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with( ['success' => ['Section updated successfully!']]);

    }
    /**
     * Method for store download app item
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
    */
    public function downloadAppItemStore(Request $request,$slug) {
        $basic_field_name = [
            'item_title'    => "required|string|max:2555",
        ];

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"download-app-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug    = Str::slug(SiteSectionConst::DOWNLOAD_APP_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();

        $validator  = Validator::make($request->all(),[
            'link'            => "required|url",
            'image'           => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','download-app-add');
        $validated = $validator->validate();

        $section_data['items'][$unique_id]['language']     = $language_wise_data;
        $section_data['items'][$unique_id]['id']           = $unique_id;
        $section_data['items'][$unique_id]['image']        = "";
        $section_data['items'][$unique_id]['link']        = $validated['link'];
        $section_data['items'][$unique_id]['created_at']   = now();
        if($request->hasFile("image")) {
            $section_data['items'][$unique_id]['image']    = $this->imageValidate($request,"image",$section->value->items->image ?? null);
        }

        $update_data['key']     = $slug;
        $update_data['value']   = $section_data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return back()->with(['success' => ['Section item added successfully!']]);
    }
    /**
     * Method for update download app item
     * @param string $slug
     * @return view
     */
    public function downloadAppItemUpdate(Request $request,$slug){
        $request->validate([
            'target'           => 'required|string',
        ]);

        $basic_field_name      = [
            'item_title_edit'     => "required|string|max:2555",
        ];

        $slug    = Str::slug(SiteSectionConst::DOWNLOAD_APP_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid!']]);

        $request->merge(['old_image' => $section_values['items'][$request->target]['image'] ?? null]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"download-app-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

        $language_wise_data = array_map(function($language) {
            return replace_array_key($language,"_edit");
        },$language_wise_data);
        $validator      = Validator::make($request->all(),[
            'link'      => "required|url",
            'image'     => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','download-app-edit');
        $validated = $validator->validate();

        $section_values['items'][$request->target]['language']      = $language_wise_data;
        $section_values['items'][$request->target]['link']          = $validated['link'];

        if($request->hasFile("image")) {
            $section_values['items'][$request->target]['image']    = $this->imageValidate($request,"image",$section_values['items'][$request->target]['image'] ?? null);
        }
        try{
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return back()->with(['success' => ['Information updated successfully!']]);
    }
    /**
     * Method for delete download app item
     * @param string $slug
     * @return view
     */
    public function downloadAppItemDelete(Request $request,$slug){
        $request->validate([
            'target'     => 'required|string',
        ]);

        $slug         = Str::slug(SiteSectionConst::DOWNLOAD_APP_SECTION);
        $section      = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid!']]);

        try{
            $image_name = $section_values['items'][$request->target]['image'];
            unset($section_values['items'][$request->target]);
            $image_path = get_files_path('site-section') . '/' . $image_name;
            delete_file($image_path);
            $section->update([
                'value'    => $section_values,
            ]);

        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Section item deleted successfully!']]);
    }
    /**
     * Method for show statistic section
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     */
    public function statisticView($slug){
        $page_title     = "Statistic Section";
        $section_slug   = Str::slug(SiteSectionConst::STATISTIC_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.statistic-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Method for update statistics section
     * @param string
     * @param \Illuminate\\Http\Request $request
     */
    
    public function statisticUpdate(Request $request,$slug){
        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string|max:100',
            'sub_heading' => 'required|string',
        ];

        $slug             = Str::slug(SiteSectionConst::STATISTIC_SECTION);
        $section          = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();
       
        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with( ['success' => ['Section updated successfully!']]);

    }
    /**
     * Method for store statistics item
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function statisticItemStore(Request $request,$slug) {
        $basic_field_name   = [
            'title'       => "required|string|max:255",
        ];

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"statistic-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug    = Str::slug(SiteSectionConst::STATISTIC_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id  = uniqid();

        $validator  = Validator::make($request->all(),[
            'counter_value'     => "required|string",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','statistic-add');
        $validated = $validator->validate();

        $section_data['items'][$unique_id]['language']          = $language_wise_data;
        $section_data['items'][$unique_id]['id']                = $unique_id;
        $section_data['items'][$unique_id]['status']            = 1;
        $section_data['items'][$unique_id]['counter_value']     = $validated['counter_value'];
        

        $update_data['key']     = $slug;
        $update_data['value']   = $section_data;
        
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return back()->with(['success' => ['Section item added successfully!']]);
    }
    /**
     * Method for update statistics item section page
     * @param string $slug
     * @return view
     */
    public function statisticItemUpdate(Request $request,$slug){
        $request->validate([
            'target'           => 'required|string',
        ]);

        $basic_field_name      = [
            "title_edit"  => "required|string|max:100",
        ];
        
        $slug        = Str::slug(SiteSectionConst::STATISTIC_SECTION);
        $section     = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values["items"])) return back()->with(['error' => ['Section item not found']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid!']]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"statistic-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
         
        $language_wise_data = array_map(function($language){
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $validator  = Validator::make($request->all(),[
            'counter_value_edit'   => "required|string|max:100",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','statistic-edit');
        $validated = $validator->validate();

        $section_values['items'][$request->target]['language']          = $language_wise_data;
        $section_values['items'][$request->target]['counter_value']     = $validated['counter_value_edit'];
        
       
       try{
            $section->update([
                'value' => $section_values,
            ]);
       }catch(Exception $e){
            return back()->with(['error' => ['Something Went wrong! Please try again.']]);
       }
       return back()->with(['success' => ['Section item updated successfully!']]);
    }
    /**
     * Method for update statistics status section page
     * @param string $slug
     * @return view
     */
    public function statisticStatusUpdate(Request $request,$slug) {
        
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        
        if ($validator->stopOnFirstFailure()->fails()) {
            return Response::error($validator->errors()->all(),null,400);
        }

        $slug           = Str::slug(SiteSectionConst::STATISTIC_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }
        if(array_key_exists("items",$data) && array_key_exists($request->data_target,$data['items'])) {
            $data['items'][$request->data_target]['status'] = ($request->status == 1) ? 0 : 1;
        }else {
            return Response::error(['Items not found or invalid!'],[],404);
        }

        $section->update([
            'value'     => $data,
        ]);

        return Response::success(['Section item status updated successfully!'],[],200);
        
    }
    /**
     * Method for delete statistics item section information
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function statisticItemDelete(Request $request,$slug){
        $request->validate([
            'target'  => 'required|string',
        ]);

        $slug         = Str::slug(SiteSectionConst::STATISTIC_SECTION);
        $section      = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid!']]);

        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success'   => ['Section item deleted successfully!']]);
    }
    /**
     * Mehtod for show call to action section page
     * @param string $slug
     * @return view
     */
    public function callToActionView($slug) {
        $page_title     = "Call To Action Section";
        $section_slug   = Str::slug(SiteSectionConst::CALL_TO_ACTION_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.call-to-action-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }

    /**
     * Mehtod for update call to action section information
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function callToActionUpdate(Request $request,$slug) {
        $basic_field_name   = [
            'heading'       => "required|string|max:100",
            'sub_heading'   => "required|string|max:255",
            'button_name'   => "required|string|max:50",
        ];

        $slug = Str::slug(SiteSectionConst::CALL_TO_ACTION_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }

        $validator  = Validator::make($request->all(),[
            'image'             => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();
        $validated = $validator->validate();

        $data['image']          = $section->value->image ?? "";

        if($request->hasFile("image")) {
            $data['image']              = $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;
        
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }

        return back()->with(['success' => ['Section updated successfully!']]);
    }
    /**
     * Method for show footer section
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     */
    public function footerView($slug){
        $page_title     = "Footer Section";
        $section_slug   = Str::slug(SiteSectionConst::FOOTER_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.footer-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Method for update footer section 
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function footerUpdate(Request $request,$slug) {
        $slug      = Str::slug(SiteSectionConst::FOOTER_SECTION);
        $section   = SiteSections::where('key',$slug)->first();
        if($section != null){
            $data  = json_decode(json_encode($section->value),true);
        }else{
            $data = [];
        }

        $basic_field_name = [
            'description'   => "required|string|max:255",
        ];

        $data['footer']['language']   = $this->contentValidate($request,$basic_field_name);

        $validated = Validator::make($request->all(),[
            'icon'                 => "nullable|array",
            'icon.*'               => "nullable|string|max:200",
            'link'                 => "nullable|array",
            'link.*'               => "nullable|url|max:255",
        ])->validate();

        $social_links = [];
        foreach($validated['icon'] ?? [] as $key => $icon) {
            $social_links[] = [
                'icon'          => $icon,
                'link'          => $validated['link'][$key] ?? "",
            ];
        }
        $data['social_links']         = $social_links;
        $data['footer']['image']      = $section->value->footer->image ?? "";
        if($request->hasFile("image")) {
            $data['footer']['image']  = $this->imageValidate($request,"image",$section->value->footer->image ?? null);
        }
        try{
            SiteSections::updateOrCreate(['key' => $slug],[
                'key'   => $slug,
                'value' => $data,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }
        return back()->with(['success' => ['Section updated successfully!']]);
    }
    /**
     * Method for show newsLetter section page
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function newsLetterView($slug) {
        $page_title     = "News Letter Section";
        $section_slug   = Str::slug(SiteSectionConst::NEWSLETTER_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.news-letter-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Mehtod for update newsLetter section
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
    */
    public function newsLetterUpdate(Request $request,$slug) {
        
        $basic_field_name    = [
            'title'          => 'required|string|max:100',
            'description'    => 'required|string',
            
        ];
        $slug           = Str::slug(SiteSectionConst::NEWSLETTER_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }

        $data['language']      = $this->contentValidate($request,$basic_field_name);
        $update_data['key']    = $slug;
        $update_data['value']  = $data;
     
        try{
            SiteSections::updateOrCreate(['key'=>$slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error'=>'Something went wrong! Please try again.']);
        }
        return back()->with(['success'  =>  ['Section updated successfully!']]);

    }
    /**
     * Method for show about section
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     */
    public function aboutView($slug){
        $page_title     = "About Section";
        $section_slug   = Str::slug(SiteSectionConst::ABOUT_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.about-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Method for update about section
     * @param string
     * @param \Illuminate\\Http\Request $request
     */
    
    public function aboutUpdate(Request $request,$slug){
        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string|max:100',
            'sub_heading' => 'required|string',
        ];

        $slug             = Str::slug(SiteSectionConst::ABOUT_SECTION);
        $section          = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();
       
        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with( ['success' => ['Section updated successfully!']]);

    }
    /**
     * Mehtod for show faq section page
     * @param string $slug
     * @return view
    */
    public function faqView($slug){
        $page_title   = "Faq Section";
        $section_slug = Str::slug(SiteSectionConst::FAQ_SECTION); 
        $data         = SiteSections::getData($section_slug)->first();
        $languages    = $this->languages;

        return view('admin.sections.setup-sections.faq-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Mehtod for update faq section information
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
    */
    public function faqUpdate(Request $request,$slug) {
        
        $basic_field_name   = [
            'title'         => 'required|string|max:100',
            'heading'       => 'required|string|max:100',
        ];

        $slug           = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }
        

        $data['language']      = $this->contentValidate($request,$basic_field_name);
        $update_data['key']    = $slug;
        $update_data['value']  = $data;
        
        try{
            SiteSections::updateOrCreate(['key'=>$slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error'=>'Something went wrong! Please try again.']);
        }
        return back()->with(['success'  =>  ['Section updated successfully!']]);

    }
    /**
     * Mehtod for store faq item information
     * @param string $slug
     * @param \Illuminate\Http\Request $request
    */
    public function faqItemStore(Request $request,$slug) {
        $basic_field_name  = [
            'question'     => "required|string|max:255",
            'answer'       => "required|string|max:500",
            
        ];

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"faq-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();
        $default =get_default_language_code();
        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['status']   = 1;
        $section_data['items'][$unique_id]['id']       = $unique_id;

        $update_data['key']     = $slug;
        $update_data['value']   = $section_data;
       
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return back()->with(['success'   => ['Section item added successfully!']]);
    }
    /**
     * Mehtod for update faq item information
     * @param string $slug
     * @param \Illuminate\Http\Request $request
    */
    public function faqItemUpdate(Request $request,$slug) {
        $request->validate([
            'target'         =>'required|string',
        ]);

        $basic_field_name = [
            'question_edit'  => "required|string|max:255",
            'answer_edit'    => "required|string|max:500",
        ];

        $slug              = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section           = SiteSections::getData($slug)->first();

        if(!$section) return back()->with(['error' => ['Section Not Found!']]);
        $section_values    = json_decode(json_encode($section->value),true);
        
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section Item Not Found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['[error' => ['Section Item is invalid']]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"faq-edit");
        
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $language_wise_data = array_map(function($language){
            return replace_array_key($language,'_edit');
        },$language_wise_data);
        
        $section_values['items'][$request->target]['language'] = $language_wise_data;

        try{
            $section->update([
                'value'  =>$section_values,
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success'   => ['Information updated successfully!']]);    
    }
    /**
     * Mehtod for delete faq item information
     * @param string $slug
     * @return view
     */
    public function faqItemDelete(request $request,$slug){
        $request->validate([
            'target'    =>'required|string',
        ]);

        $slug           = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section        = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);

        if(!isset($section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item not found!']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e){
            return $e->getMessage();
        }
        return back()->with(['success' => ['Section item deleted successfully!']]);
    }
    /**
     * Mehtod for update faq item status 
     * @param string $slug
     * @return view
     */
    public function faqStatusUpdate(Request $request,$slug) {
        
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        
        if ($validator->stopOnFirstFailure()->fails()) {
            return Response::error($validator->errors()->all(),null,400);
        }

        $slug           = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }
        if(array_key_exists("items",$data) && array_key_exists($request->data_target,$data['items'])) {
            $data['items'][$request->data_target]['status'] = ($request->status == 1) ? 0 : 1;
        }else {
            return Response::error(['Items not found or invalid!'],[],404);
        }

        $section->update([
            'value'     => $data,
        ]);

        return Response::success(['Section item status updated successfully!'],[],200);
        
    }
    /**
     * Method for show service section page
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     */
    public function serviceView($slug){
        $page_title     = "Service Section";
        $section_slug   = Str::slug(SiteSectionConst::SERVICE_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;
        
        return view('admin.sections.setup-sections.service-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Method for update howItsWork section page
     * @param string $slug
     * @return view
     */
    public function serviceUpdate(Request $request,$slug){

        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string|max:100',
        ];

        $slug     = Str::slug(SiteSectionConst::SERVICE_SECTION);
        $section  = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data    = json_decode(json_encode($section->value),true);
        }else{
            $data    =[];
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(["key"=>$slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Section updated successfully!']]);
     
    }
    /**
     * Method for store service item
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function serviceItemStore(Request $request,$slug) {
        $basic_field_name = [
            'item_title'       => 'required|string|max:100',
            'item_heading'       => 'required|string|max:500',
        ];


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"service-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        
        $slug       = Str::slug(SiteSectionConst::SERVICE_SECTION);
        $section    = SiteSections::where('key',$slug)->first();
        if($section != null){
            $section_data = json_decode(json_encode($section->value),true);
        }else{
            $section_data = [];
        }
        $unique_id =uniqid();

        $validator  = Validator::make($request->all(),[
            'icon'            => "required|string|max:100",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','service-add');
        $validated = $validator->validate();

        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['status']   = 1;
        $section_data['items'][$unique_id]['id']       = $unique_id;
        $section_data['items'][$unique_id]['icon']     = $validated['icon'];

        $update_data['key']   = $slug;
        $update_data['value'] = $section_data;
        
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }
        return back()->with(['success' => ['Section item added successfully!']]);
    }
    
    /**
     * Method for update service item
     * @param string $slug
     * @return view
     */
    public function serviceItemUpdate(Request $request,$slug){
        $request->validate([
            'target'           => 'required|string',
        ]);

        $basic_field_name      = [
            "item_title_edit"  => "required|string|max:100",
            "item_heading_edit"  => "required|string|max:500",
        ];
        
        $slug        = Str::slug(SiteSectionConst::SERVICE_SECTION);
        $section     = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values["items"])) return back()->with(['error' => ['Section item not found']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid!']]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"service-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
         
        $language_wise_data = array_map(function($language){
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $validator  = Validator::make($request->all(),[
            'icon_edit'            => "required|string|max:100",
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','service-edit');
        $validated = $validator->validate();

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        $section_values['items'][$request->target]['icon']     = $validated['icon_edit'];
        
       
       try{
            $section->update([
                'value' => $section_values,
            ]);
       }catch(Exception $e){
            return back()->with(['error' => ['Something Went wrong! Please try again.']]);
       }
       return back()->with(['success' => ['Section item updated successfully!']]);
    }
    /**
     * Method for delete service item
     * @param string $slug
     * @return view
     */
    public function serviceItemDelete(Request $request,$slug){
        $request->validate([
            'target' => 'required|string',
        ]);

        $slug     = Str::slug(SiteSectionConst::SERVICE_SECTION);
        $section  = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => ['Section not found!']]);
        $section_values  = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => ['Section Item not Found!']]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['Section item is invalid']]);

        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Section item deleted successfully!']]);    
    }
    /**
     * Mehtod for update service item status 
     * @param string $slug
     * @return view
     */
    public function serviceStatusUpdate(Request $request,$slug) {
        
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        
        if ($validator->stopOnFirstFailure()->fails()) {
            return Response::error($validator->errors()->all(),null,400);
        }

        $slug           = Str::slug(SiteSectionConst::SERVICE_SECTION);
        $section        = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data       = json_decode(json_encode($section->value),true);
        }else{
            $data       = [];
        }
        if(array_key_exists("items",$data) && array_key_exists($request->data_target,$data['items'])) {
            $data['items'][$request->data_target]['status'] = ($request->status == 1) ? 0 : 1;
        }else {
            return Response::error(['Items not found or invalid!'],[],404);
        }

        $section->update([
            'value'     => $data,
        ]);

        return Response::success(['Section item status updated successfully!'],[],200);
        
    }
    /**
     *  Method for show journal section page
     * @param string $slug
     * @return view
     */
    public function blogView($slug){
        $page_title         = "Blog Section";
        $section_slug       = Str::slug(SiteSectionConst::BLOG_SECTION);
        $data               = SiteSections::getData($section_slug)->first();
        $languages          = $this->languages;
        $category           = BlogCategory::get();
        $active_category    = BlogCategory::where('status',true)->get();
        $blog               = Blog::orderByDesc("id")->get();
        $blog_active        = Blog::where('status',true)->get();
        $blog_deactive      = Blog::where('status',false)->get();


        return view('admin.sections.setup-sections.blog-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
            'category',
            'active_category',
            'blog',
            'blog_active',
            'blog_deactive',
        ));
    }
    /**
     * Mehtod for update webJournal section page
     * @param string $slug
     * @return view
     */
    public function blogUpdate(Request $request,$slug){

        $basic_field_name       = [
            'title'             => 'required|string|max:100',
            'heading'           => 'required|string|max:300',
        ];

        $slug     = Str::slug(SiteSectionConst::BLOG_SECTION);
        $section  = SiteSections::where("key",$slug)->first();
        if($section != null ){
            $data    = json_decode(json_encode($section->value),true);
        }else{
            $data    =[];
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        

        try{
            SiteSections::updateOrCreate(["key"=>$slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Section updated successfully!']]);
     
    }
    /**
     * Method for show contact section
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     */
    public function contactView($slug){
        $page_title     = "Contact Section";
        $section_slug   = Str::slug(SiteSectionConst::CONTACT_SECTION);
        $data           = SiteSections::getData($section_slug)->first();
        $languages      = $this->languages;

        return view('admin.sections.setup-sections.contact-section',compact(
            'page_title',
            'data',
            'languages',
            'slug'
        ));
    }
    /**
     * Method for update contact section
     * @param string
     * @param \Illuminate\\Http\Request $request
     */
    
    public function contactUpdate(Request $request,$slug){
        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string',
        ];

        $slug             = Str::slug(SiteSectionConst::CONTACT_SECTION);
        $section          = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();
       
        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with( ['success' => ['Section updated successfully!']]);

    }
/**
     * Method for show login section page
     * @param string $slug
     * @return view
     */
    public function loginView($slug){
        $page_title      = "Login Section";
        $section_slug    = Str::slug(SiteSectionConst::LOGIN_SECTION);
        $data            = SiteSections::getData($section_slug)->first();
        $languages       = $this->languages;

        return view('admin.sections.setup-sections.login-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    /**
     * Method for update login section
     * @param string
     * @param \Illuminate\\Http\Request $request
     */
    
    public function loginUpdate(Request $request,$slug){
        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string',
        ];

        $slug             = Str::slug(SiteSectionConst::LOGIN_SECTION);
        $section          = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();

        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with( ['success' => ['Section updated successfully!']]);

    }
    /**
     * Method for show register section page
     * @param string $slug
     * @return view
     */
    public function registerView($slug){
        $page_title      = "Register Section";
        $section_slug    = Str::slug(SiteSectionConst::REGISTER_SECTION);
        $data            = SiteSections::getData($section_slug)->first();
        $languages       = $this->languages;

        return view('admin.sections.setup-sections.register-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    /**
     * Method for update register section
     * @param string
     * @param \Illuminate\\Http\Request $request
     */
    
    public function registerUpdate(Request $request,$slug){
        $basic_field_name = [
            'title'       => 'required|string|max:100',
            'heading'     => 'required|string',
        ];

        $slug             = Str::slug(SiteSectionConst::REGISTER_SECTION);
        $section          = SiteSections::where("key",$slug)->first();

        if($section      != null){
            $data         = json_decode(json_encode($section->value),true);
        }else{
            $data         = [];
        }
        $validator  = Validator::make($request->all(),[
            'image'            => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()) return back()->withErrors($validator->errors())->withInput();

        $validated = $validator->validate();

        $data['image']    = $section->value->image ?? "";

        if($request->hasFile("image")){
            $data['image']= $this->imageValidate($request,"image",$section->value->image ?? null);
        }

        $data['language']     = $this->contentValidate($request,$basic_field_name);
        $update_data['key']   = $slug;
        $update_data['value'] = $data;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with( ['success' => ['Section updated successfully!']]);

    }
    /**
     * Method for get languages form record with little modification for using only this class
     * @return array $languages
     */
    public function languages() {
        $languages = Language::whereNot('code',LanguageConst::NOT_REMOVABLE)->select("code","name")->get()->toArray();
        $languages[] = [
            'name'      => LanguageConst::NOT_REMOVABLE_CODE,
            'code'      => LanguageConst::NOT_REMOVABLE,
        ];
        return $languages;
    }

    /**
     * Method for validate request data and re-decorate language wise data
     * @param object $request
     * @param array $basic_field_name
     * @return array $language_wise_data
     */
    public function contentValidate($request,$basic_field_name,$modal = null) {
        $languages = $this->languages();

        $current_local = get_default_language_code();
        $validation_rules = [];
        $language_wise_data = [];
        foreach($request->all() as $input_name => $input_value) {
            foreach($languages as $language) {
                $input_name_check = explode("_",$input_name);
                $input_lang_code = array_shift($input_name_check);
                $input_name_check = implode("_",$input_name_check);
                if($input_lang_code == $language['code']) {
                    if(array_key_exists($input_name_check,$basic_field_name)) {
                        $langCode = $language['code'];
                        if($current_local == $langCode) {
                            $validation_rules[$input_name] = $basic_field_name[$input_name_check];
                        }else {
                            $validation_rules[$input_name] = str_replace("required","nullable",$basic_field_name[$input_name_check]);
                        }
                        $language_wise_data[$langCode][$input_name_check] = $input_value;
                    }
                    break;
                } 
            }
        }
        if($modal == null) {
            $validated = Validator::make($request->all(),$validation_rules)->validate();
        }else {
            $validator = Validator::make($request->all(),$validation_rules);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with("modal",$modal);
            }
            $validated = $validator->validate();
        }

        return $language_wise_data;
    }

    /**
     * Method for validate request image if have
     * @param object $request
     * @param string $input_name
     * @param string $old_image
     * @return boolean|string $upload
     */
    public function imageValidate($request,$input_name,$old_image) {
        if($request->hasFile($input_name)) {
            $image_validated = Validator::make($request->only($input_name),[
                $input_name         => "image|mimes:png,jpg,webp,jpeg,svg",
            ])->validate();

            $image = get_files_from_fileholder($request,$input_name);
            $upload = upload_files_from_path_dynamic($image,'site-section',$old_image);
            return $upload;
        }

        return false;
    }
}
