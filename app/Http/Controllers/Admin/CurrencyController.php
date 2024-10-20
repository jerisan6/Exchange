<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Currency;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Helpers\Response;
use App\Models\Admin\CurrencyHasNetwork;
use App\Models\Admin\Network;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Setup Currency";
        $currencies = Currency::orderByDesc('default')->paginate(10);
        $networks   = Network::where('status',true)->orderByDesc('id')->get();

        return view('admin.sections.currency.index',compact(
            'page_title',
            'currencies',
            'networks'
        ));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $validator          = Validator::make($request->all(),[
            'name'          => 'required|string',
            'code'          => 'required|string|unique:currencies',
            'symbol'        => 'required|string',
            'option'        => 'required|string',
            'flag'          => 'nullable|image|mimes: jpg,png,jpeg,svg,webp',
            'rate'          => 'required',
            'network'       => 'required|array',
            'network.*'     => 'required|string',
            'fees'          => 'nullable|array',
            'fees.*'        => 'nullable|string',
            
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','currency_add');
        }
        $validated = $validator->validate();

       
        
        $default = [
            'default' => true,
            'optional'  => false,
        ];

        // If Default is already available
        if($default[$validated['option']] == true) {
            $check_default = Currency::where('default',true);
            if($check_default->count() > 0) {
                try{
                    $check_default->update([
                        'default'       => false,
                    ]);
                }catch(Exception $e) {
                    return back()->with(['error' => ['Default currency make faild! Please try again.']]);
                }
            }
        }

        
        $validated['default']       = $default[$validated['option']];
        $validated['created_at']    = now();
        $validated['admin_id']      = Auth::user()->id;
        $validated['sender']        = true;
        $validated['receiver']      = true;
        $network                    = $validated['network'];
        
        $validated = Arr::except($validated,['role','flag','option','network']);
        // insert_data
        try{
            $currency = Currency::create($validated);
            if(count($network) > 0){
                $networks = [];
                foreach($network as $key => $network_id){
                    
                    $networks[] = [
                        'currency_id'   => $currency->id,
                        'network_id'    => $network_id,
                        'created_at'    => now(),
                    ];
                }
                
                $collection = collect($networks);
                $uniqueNetwork = $collection->unique('network_id')->values()->all();
                CurrencyHasNetwork::insert($uniqueNetwork);
            }
        }catch(Exception $e) {
            
            return back()->withErrors($validator)->withInput()->with(['error' => ['Something went wrong! Please try again.']]);
        }

        // Uplaod File
        if($request->hasFile('flag')) {
            try{
                $image = get_files_from_fileholder($request,'flag');
                $uploadFlag = upload_files_from_path_dynamic($image,'currency-flag');
    
                // Update Database
                $currency->update([
                    'flag'  => $uploadFlag,
                ]);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => ['Something went wrong! Please try again.']]);
            }
        }

        return back()->with(['success' => ['Currency Saved Successfully!']]);
    }

    
    /**
     * Update Currency Status
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        $currency_code = $validated['data_target'];

        $currency = Currency::where('code',$currency_code)->first();
        if(!$currency) {
            $error = ['error' => ['Currency record not found in our system.']];
            return Response::error($error,null,404);
        }

        try{
            $currency->update([
                'status' => ($validated['status'] == true) ? false : true,
            ]);
        }catch(Exception $e) {
            $error = ['error' => ['Something went wrong!. Please try again.']];
            return Response::error($error,null,500);
        }

        $success = ['success' => ['Currency status updated successfully!']];
        return Response::success($success,null,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $target = $request->target ?? $request->currency_code;
        $currency = Currency::where('code',$target)->first();
        if(!$currency) {
            return back()->with(['warning' => ['Currency not found!']]);
        }
        $request->merge(['old_flag' => $currency->flag]);

        $validator = Validator::make($request->all(),[
            
            'currency_name'      => 'required|string',
            'currency_code'      => ['required','string',Rule::unique('currencies','code')->ignore($currency->id)],
            'currency_symbol'    => 'required|string',
            'currency_rate'      => 'required|numeric',
            'currency_option'    => 'required|string',
            'currency_target'    => 'nullable|string',
            'network'            => 'required|array',
            'network.*'          => 'required|string',
            'fees'               => 'nullable|array',
            'fees.*'             => 'nullable|string',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','currency_edit');
        }
        
        $validated = $validator->validate();

        $default = [
            '1' => true,
            '0'  => false,
        ];

        // If Default is already available
        if($default[$validated['currency_option']] == true) {
            $check_default = Currency::where('default',true);
            if($check_default->count() > 0) {
                try{
                    $check_default->update([
                        'default'       => false,
                    ]);
                }catch(Exception $e) {
                    return back()->with(['error' => ['Default currency make faild! Please try again.']]);
                }
            }
        }
        $validated['currency_default']   = $default[$validated['currency_option']];
        $network                    = $validated['network'];
        
        $validated = Arr::except($validated,['currency_role','currency_flag','currency_option','network']);

        if($request->hasFile('currency_flag')) {
            try{
                $image = get_files_from_fileholder($request,'currency_flag');
                $uploadFlag = upload_files_from_path_dynamic($image,'currency-flag',$currency->flag);
                $validated['currency_flag'] = $uploadFlag;
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => ['Image file upload faild!']]);
            }
        }
        $validated = replace_array_key($validated,"currency_");
        try{
            $currency_network_id    = $currency->networks->pluck('id');
            CurrencyHasNetwork::whereIn('id',$currency_network_id)->delete();
            
            $currency->update($validated);
            if(count($network) > 0){
                $networks = [];
                foreach($network as $key => $network_id){
                    
                    $networks[] = [
                        'currency_id'   => $currency->id,
                        'network_id'    => $network_id,
                        'created_at'    => now(),
                    ];
                }
                
                $collection = collect($networks);
                $uniqueNetwork = $collection->unique('network_id')->values()->all();
                CurrencyHasNetwork::insert($uniqueNetwork);
            }
        }catch(Exception $e) {
            return back()->withErrors($validator)->withInput()->with(['error' => ['Something went wrong! Please try again.']]);
        }

        return back()->with(['success' => ['Successfully updated the information.']]);
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:currencies,code',
        ]);
        $validated = $validator->validate();
        $currency = Currency::where("code",$validated['target'])->first();
        $networks   = CurrencyHasNetwork::where('currency_id',$currency->id)->get();
        

        if($currency->isDefault()) {
            return back()->with(['warning' => ['Can\'t deletable default currency.']]);
        }

        try{
            $currency->delete();
            foreach ($networks as $network) {
                $network->delete();
            }
            delete_file(get_files_path('currency-flag').'/'.$currency->flag);
        }catch(Exception $e) {
            
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }

        return back()->with(['success' => ['Currency deleted successfully!']]);
    }

    public function search(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);

        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        $currencies = Currency::search($validated['text'])->select()->limit(10)->get();
        return view('admin.components.search.currency-search',compact(
            'currencies',
        ));
    }
}
