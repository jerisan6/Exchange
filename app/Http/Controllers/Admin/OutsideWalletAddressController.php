<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Http\Controllers\Controller;
use App\Models\Admin\CurrencyHasNetwork;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\OutsideWalletAddress;
use Illuminate\Validation\ValidationException;

class OutsideWalletAddressController extends Controller
{
    /**
     * Method for outside wallet index page
     * @return view
     */
    public function index(){
        $page_title         = "Outside Wallet Address";
        $outside_wallets    = OutsideWalletAddress::with(['currency','network'])->orderBy('id','desc')->get();

        return view('admin.sections.outside-wallet.index',compact(
            'page_title',
            'outside_wallets'
        ));
    }
    /**
     * Method for outside wallet create page
     * @return view
     */
    public function create(){
        $page_title         = "Outside Wallet Create";
        $currencies         = Currency::with(['networks'])->where('status',true)->orderBy('id')->get();
        
        return view('admin.sections.outside-wallet.create',compact(
            'page_title',
            'currencies'
        ));
    } 
    /**
     * Method for get all Networks based on Currency
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     */
    public function getNetworks(Request $request) {

        $validator    = Validator::make($request->all(),[
            'currency'  => 'required|integer',           
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        $currency  = Currency::with(['networks' => function($network) {
            $network->with(['network']);
        }])->find($request->currency);
        if(!$currency) return Response::error(['Currency Not Found'],404);

        return Response::success(['Data fetch successfully'],['currency' => $currency],200);
    }
    /**
     * Method for outside wallet store 
     */
    public function store(Request $request){
        
        $validator                  = Validator::make($request->all(),[
            'currency'              => 'required',
            'network'               => 'required',
            'public_address'        => 'required|string|max:250',
            'desc'                  => 'nullable',
            'label'                 => 'nullable|array',
            'label.*'               => 'nullable|string|max:50',
            'input_type'            => 'nullable|array',
            'input_type.*'          => 'nullable|string|max:20',
            'min_char'              => 'nullable|array',
            'min_char.*'            => 'nullable|numeric',
            'max_char'              => 'nullable|array',
            'max_char.*'            => 'nullable|numeric',
            'field_necessity'       => 'nullable|array',
            'field_necessity.*'     => 'nullable|string|max:20',
            'file_extensions'       => 'nullable|array',
            'file_extensions.*'     => 'nullable|string|max:255',
            'file_max_size'         => 'nullable|array',
            'file_max_size.*'       => 'nullable|numeric',
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        $validated                  = $validator->validate();
        if(OutsideWalletAddress::where('currency_id',$validated['currency'])->where('network_id',$validated['network'])->exists()){
            throw ValidationException::withMessages([
                'name'  => "Outside Address already exists in selected currency and network!",
            ]);
        }
        if(OutsideWalletAddress::where('public_address',$validated['public_address'])->exists()){
            throw ValidationException::withMessages([
                'name'  => "Outside Public Address already exists!",
            ]);
        }

        $validated['currency_id']       = $validated['currency'];
        $validated['network_id']        = $validated['network'];
        $validated['slug']              = Str::slug($request->public_address);
        $validated['public_address']    = $validated['public_address'];
        $validated['desc']              = $validated['desc'];
        $validated['input_fields']      = decorate_input_fields($validated);
        
        $validated = Arr::except($validated,['label','input_type','min_char','max_char','field_necessity','file_extensions','file_max_size']);
        
        try{
            OutsideWalletAddress::create($validated);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('admin.outside.wallet.index')->with(['success' => ['Outside wallet created successfully.']]);
    }
    /**
     * Method for edit outside wallet address
     * @param $public_address
     * @return view
     */
    public function edit($public_address){
        $page_title         = "Outside Wallet Edit";
        $data               = OutsideWalletAddress::with(['currency','network'])->where('public_address',$public_address)->first();
        $currencies         = Currency::with(['networks'])->where('id',$data->currency_id)->where('status',true)->first();
        $networks           = CurrencyHasNetwork::with(['network'])->where('currency_id',$data->currency_id)->orderBy('id')->get();
        
        if(!$data) return back()->with(['error' => ['Sorry!Data not found!']]);
        
        return view('admin.sections.outside-wallet.edit',compact(
            'page_title',
            'currencies',
            'networks',
            'data'
        ));
    }
    /**
     * Method for update outside wallet 
     * @param $public_address
     * @param \Illuminate\Http\Request $request
     */
    public function update(Request $request,$public_address){
        $data           = OutsideWalletAddress::where('public_address',$public_address)->first();
        $validator                  = Validator::make($request->all(),[
            'currency'              => 'required',
            'network'               => 'required',
            'public_address'        => 'required|string|max:250',
            'desc'                  => 'nullable',
            'label'                 => 'nullable|array',
            'label.*'               => 'nullable|string|max:50',
            'input_type'            => 'nullable|array',
            'input_type.*'          => 'nullable|string|max:20',
            'min_char'              => 'nullable|array',
            'min_char.*'            => 'nullable|numeric',
            'max_char'              => 'nullable|array',
            'max_char.*'            => 'nullable|numeric',
            'field_necessity'       => 'nullable|array',
            'field_necessity.*'     => 'nullable|string|max:20',
            'file_extensions'       => 'nullable|array',
            'file_extensions.*'     => 'nullable|string|max:255',
            'file_max_size'         => 'nullable|array',
            'file_max_size.*'       => 'nullable|numeric',
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        $validated                      = $validator->validate();
        if(OutsideWalletAddress::whereNot('id',$data->id)->where('currency_id',$validated['currency'])->where('network_id',$validated['network'])->exists()){
            throw ValidationException::withMessages([
                'name'  => "Outside Address already exists in selected currency and network!",
            ]);
        }
        if(OutsideWalletAddress::where('public_address',$validated['public_address'])->exists()){
            throw ValidationException::withMessages([
                'name'  => "Outside Public Address already exists!",
            ]);
        }
        
        $validated['currency_id']       = $validated['currency'];
        $validated['network_id']        = $validated['network'];
        $validated['public_address']    = $validated['public_address'];
        $validated['desc']              = $validated['desc'];
        $validated['input_fields']      = decorate_input_fields($validated);
        
        $validated = Arr::except($validated,['label','input_type','min_char','max_char','field_necessity','file_extensions','file_max_size']);
        
        try{
            $data->update($validated);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('admin.outside.wallet.index')->with(['success' => ['Outside wallet Updated successfully.']]);
    }
    /**
     * Method for delete Outside Wallet
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function delete(Request $request){
        $request->validate([
            'target'    => 'required|numeric|',
        ]);
        $outside_wallet = OutsideWalletAddress::find($request->target);
    
        try {
            $outside_wallet->delete();
        } catch (Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Outside Wallet Deleted Successfully!']]);
    }
    /**
     * Method for status update for Outside wallet
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric|exists:outside_wallet_addresses,id',
            'status'            => 'required|boolean',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();


        $outside_wallet = OutsideWalletAddress::find($validated['data_target']);

        try{
            $outside_wallet->update([
                'status'        => ($validated['status']) ? false : true,
            ]);
        }catch(Exception $e) {
            $errors = ['error' => ['Something went wrong! Please try again.'] ];
            return Response::error($errors,null,500);
        }

        $success = ['success' => ['Outside Wallet status updated successfully!']];
        return Response::success($success);
    }
}
