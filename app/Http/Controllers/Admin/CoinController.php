<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Admin\Coin;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CoinController extends Controller
{
    /**
     * Method for view the coin page
     * @return view
     */
    public function index(){
        $page_title     = "Coins";
        $coins          = Coin::orderByDesc('id')->paginate(10);

        return view('admin.sections.coin.index',compact(
            'page_title',
            'coins'
        ));
    }
    /**
     * Method for store Coin 
     * @param string 
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request){
        $validator     = Validator::make($request->all(),[
            'name'     => 'required|string',
            'title'    => 'nullable|string'
        ]);

        if($validator->fails()) return back()->withErrors($validator)->withInput()->with("modal","add-coin");

        $validated     = $validator->validate();
        $validated['slug']   = Str::slug($request->name);
        if(Coin::where('slug',$validated['slug'])->exists()){
            throw ValidationException::withMessages([
                'name'   => 'Coin already exists',
            ]);
        }
        $validated['last_edit_by']  = auth()->user()->id;
        try{
            Coin::create($validated);
        }catch(Exception $e){
            return back()->with(['error'  => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Coin Added Successfully']]);
    }
    /**
     * Method for update coin 
     * @param string
     * @param \Illuminate\Http\Request $request 
     */
    public function update(Request $request){

        $validator = Validator::make($request->all(),[
            'target'        => 'required|numeric|exists:coins,id',
            'edit_name'     => 'required|string|max:150|',
            'edit_title'    => 'nullable|string|',
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with("modal","edit-coin");
        }

        $validated = $validator->validate();
        
        $slug      = Str::slug($request->edit_name);
        $validated = replace_array_key($validated,"edit_");
        $validated = Arr::except($validated,['target']);
        $validated['slug']   = $slug;
        $validated['last_edit_by']  = auth()->user()->id;
        if(Coin::where('slug',$slug)->exists()){
            throw ValidationException::withMessages([
                'name'    => 'Coin already exists',
            ]);
        }

        $coin = Coin::find($request->target);
        
        try{
            $coin->update($validated);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Coin updated successfully!']]);

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
           $coin = Coin::find($request->target);
    
        try {
            $coin->delete();
        } catch (Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Coin Deleted Successfully!']]);
    }
    /**
     * Method for status update for coin
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric|exists:coins,id',
            'status'            => 'required|boolean',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();


        $coin = Coin::find($validated['data_target']);

        try{
            $coin->update([
                'status'        => ($validated['status']) ? false : true,
            ]);
        }catch(Exception $e) {
            $errors = ['error' => ['Something went wrong! Please try again.'] ];
            return Response::error($errors,null,500);
        }

        $success = ['success' => ['Coin status updated successfully!']];
        return Response::success($success);
    }
}
