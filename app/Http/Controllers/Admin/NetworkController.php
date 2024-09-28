<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Admin\Coin;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Admin\Network;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NetworkController extends Controller
{
    /**
     * Method for view the network page
     * @return view
     */
    public function index(){
        $page_title     = "Networks";
        $coins          = Coin::where('status',true)->orderByDesc('id')->get();
        $networks       = Network::orderByDesc('id')->get();

        return view('admin.sections.network.index',compact(
            'page_title',
            'coins',
            'networks'
        ));
    }
    /**
     * Method for store network 
     * @param string 
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request){
        $validator              = Validator::make($request->all(),[
            'coin'          => 'required',
            'name'          => 'required|string',
            'arrival_time'  => 'required',
            'description'   => 'nullable|string'
        ]);

        if($validator->fails()) return back()->withErrors($validator)->withInput()->with("modal","add-network");

        $validated                  = $validator->validate();
        $validated['slug']          = Str::slug($request->name);
        $validated['coin_id']       = $validated['coin'];
        $validated['last_edit_by']  = auth()->user()->id;
        
        if(Network::where('slug',$validated['slug'])->exists()){
            throw ValidationException::withMessages([
                'name'   => 'Network already exists',
            ]);
        }
        
        try{
            Network::create($validated);
        }catch(Exception $e){
            
            return back()->with(['error'  => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Network Added Successfully']]);
    }
    /**
     * Method for update network 
     * @param string
     * @param \Illuminate\Http\Request $request 
     */
    public function update(Request $request){

        $validator                  = Validator::make($request->all(),[
            'target'                => 'required|numeric|exists:networks,id',
            'edit_coin'             => 'required',
            'edit_name'             => 'required|string|max:150|',
            'edit_arrival_time'     => 'required',
            'edit_description'      => 'nullable|string',
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with("modal","edit-network");
        }

        $validated = $validator->validate();
        
        $slug      = Str::slug($request->edit_name);
        $validated = replace_array_key($validated,"edit_");
        $validated = Arr::except($validated,['target']);
        $validated['slug']   = $slug;
        $validated['last_edit_by']  = auth()->user()->id;
        
        if(Network::where('slug',$validated['slug'])->exists()){
            throw ValidationException::withMessages([
                'name'    => 'Network already exists',
            ]);
        }

        $coin = Network::find($request->target);
        
        try{
            $coin->update($validated);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Network updated successfully!']]);

    }
    /**
     * Method for delete coin
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function delete(Request $request){
        $request->validate([
            'target'    => 'required|numeric|',
        ]);
           $networks = Network::find($request->target);
    
        try {
            $networks->delete();
        } catch (Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Network Deleted Successfully!']]);
    }
    /**
     * Method for status update for networks
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric|exists:networks,id',
            'status'            => 'required|boolean',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();


        $networks = Network::find($validated['data_target']);

        try{
            $networks->update([
                'status'        => ($validated['status']) ? false : true,
            ]);
        }catch(Exception $e) {
            $errors = ['error' => ['Something went wrong! Please try again.'] ];
            return Response::error($errors,null,500);
        }

        $success = ['success' => ['Network status updated successfully!']];
        return Response::success($success);
    }
}
