<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\websiteSubscribeNotification;

class SubscribeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title  = "Website Subscribers";
        $subscribers = Subscribe::orderByDesc("id")->paginate(10);
        return view('admin.sections.subscriber.index',compact(
            'page_title',
            'subscribers'
        ));
    }
    /**
     * Method for send mail
     */
    public function sendMail(Request $request) {
        $validator  = Validator::make($request->all(),[
            'subject'      => "required|string|max:255",
            'message'      => "required|string|max:5000",
        ]);

        if($validator->fails()) return back()->withErrors($validator)->withInput()->with('modal','subscriber-send-mail');
        $validated = $validator->validated();
        try{
            $subscribers = Subscribe::get()->pluck('email')->toArray();
            Notification::route("mail",$subscribers)->notify(new websiteSubscribeNotification($validated));
        }catch(Exception $e){
            return back()->with(['error' => ['Mail Send Failed! Please try again.']]);
        }
        return back()->with(['success' => ['Mail successfully sended']]);
    }
}
