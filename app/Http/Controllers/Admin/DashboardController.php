<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Admin\Blog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Http\Helpers\Response;
use App\Models\Admin\BlogCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\SupportTicketConst;
use App\Constants\PaymentGatewayConst;
use App\Providers\Admin\BasicSettingsProvider;
use Pusher\PushNotifications\PushNotifications;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title      = "Dashboard";

        $last_month_start   = date('Y-m-01', strtotime('-1 month', strtotime(date('Y-m-d'))));
        $last_month_end     = date('Y-m-31', strtotime('-1 month', strtotime(date('Y-m-d'))));
        $this_month_start   = date('Y-m-01');
        $this_month_end     = date('Y-m-d');

        $total_users     = (User::toBase()->count() == 0) ? 1 : User::toBase()->count();
        $unverified_user = User::toBase()->where('email_verified',0)->count();
        $active_user     = User::toBase()->where('status',true)->count();
        $banned_user     = User::toBase()->where('status',false)->count();
        $user_percent    = (($active_user * 100 ) / $total_users);
        $user_chart      = [$active_user, $banned_user,$unverified_user,$total_users];

        if ($user_percent > 100) {
            $user_percent = 100;
        }
        
        $total_ticket       = (SupportTicket::toBase()->count() == 0) ? 1 : SupportTicket::toBase()->count();
        $active_ticket      = SupportTicket::toBase()->where('status',SupportTicketConst::ACTIVE)->count();
        $pending_ticket     = SupportTicket::toBase()->where('status',SupportTicketConst::PENDING)->count();

        if($pending_ticket == 0 && $active_ticket != 0){
            $percent_ticket = 100;
        }elseif($pending_ticket == 0 && $active_ticket == 0){
            $percent_ticket = 0;
        }else{
            $percent_ticket = ($active_ticket / ($active_ticket + $pending_ticket)) * 100;
        }

        $total_categories           = (BlogCategory::toBase()->count() == 0) ? 1 : BlogCategory::toBase()->count();
        $active_category            = BlogCategory::toBase()->where('status',true)->count();
        $inactive_category          = BlogCategory::toBase()->where('status',false)->count();
        $category_percent           = (($active_category * 100) / $total_categories);
        
        if($category_percent > 100){
            $category_percent = 100;
        }

        $total_blogs           = (Blog::toBase()->count() == 0) ? 1 : Blog::toBase()->count();
        $active_blog            = Blog::toBase()->where('status',true)->count();
        $inactive_blog          = Blog::toBase()->where('status',false)->count();
        $blog_percent           = (($active_blog * 100) / $total_blogs);
        
        if($blog_percent > 100){
            $blog_percent = 100;
        }
        
        $total_transactions           = (Transaction::toBase()->count() == 0) ? 1 : Transaction::toBase()->count();
        $pending_transactions         = Transaction::toBase()->where('status',global_const()::STATUS_PENDING)->count();
        $confirm_transactions         = Transaction::toBase()->where('status',global_const()::STATUS_CONFIRM_PAYMENT)->count();
        $percent_transactions         = ((($pending_transactions + $confirm_transactions) * 100) / $total_transactions);
        
        if($percent_transactions > 100){
            $percent_transactions = 100;
        }

        $total_buy_crypto   = (Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->toBase()->count() == 0) ? 1 : Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->toBase()->count();
        $pending_buy_crypto         = Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->toBase()->where('status',global_const()::STATUS_PENDING)->count();
        $confirm_buy_crypto         = Transaction::where('type',PaymentGatewayConst::BUY_CRYPTO)->toBase()->where('status',global_const()::STATUS_CONFIRM_PAYMENT)->count();
        $percent_buy_crypto         = ((($pending_buy_crypto + $confirm_buy_crypto) * 100) / $total_buy_crypto);
        
        if($percent_buy_crypto > 100){
            $percent_buy_crypto = 100;
        }

        $total_charges      = Transaction::toBase()->sum('total_charge');
        $this_month_charge  = Transaction::toBase()->whereDate('created_at',">=" , $this_month_start)
                            ->whereDate('created_at',"<=" , $this_month_end)
                            ->sum('total_charge');

        $last_month_charge = Transaction::toBase()->whereDate('created_at',">=" , $last_month_start)
                            ->whereDate('created_at',"<=" , $last_month_end)
                            ->sum('total_charge');

        $monthlyDataBuy    = Transaction::where("type",PaymentGatewayConst::BUY_CRYPTO)->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        $monthlyDataSell    = Transaction::where("type",PaymentGatewayConst::SELL_CRYPTO)->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        $monthlyDataWithdraw    = Transaction::where("type",PaymentGatewayConst::WITHDRAW_CRYPTO)->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        $monthlyDataExchange    = Transaction::where("type",PaymentGatewayConst::EXCHANGE_CRYPTO)->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
        
        // Prepare data for the chart
        $labels         = [];
        $buy_data       = [];
        $sell_data      = [];
        $withdraw_data  = [];
        $exchange_data  = [];
        
        // Create an array with all months 
        $monthsArray            = array_fill_keys(range(1, 12), 0);
        $monthsArraySell        = array_fill_keys(range(1, 12), 0);
        $monthsArrayWithdraw    = array_fill_keys(range(1, 12), 0);
        $monthsArrayExchange    = array_fill_keys(range(1, 12), 0);
        
        
        foreach ($monthlyDataBuy as $record) {
            $monthsArray[$record->month] = $record->total;
        }

        foreach ($monthlyDataSell as $record) {
            $monthsArraySell[$record->month] = $record->total;
        }

        foreach ($monthlyDataWithdraw as $record) {
            $monthsArrayWithdraw[$record->month] = $record->total;
        }
        foreach ($monthlyDataExchange as $record) {
            $monthsArrayExchange[$record->month] = $record->total;
        }
        
        foreach ($monthsArray as $month => $count) {
            $monthName = date('M', mktime(0, 0, 0, $month, 1)); // Full month name
            $labels[] = $monthName;
            $buy_data[] = $count;
        }

        foreach ($monthsArraySell as $month => $count) {
            $sell_data[]    = $count;
        }

        foreach ($monthsArrayWithdraw as $month => $count) {
            $withdraw_data[]    = $count;
        }
        foreach ($monthsArrayExchange as $month => $count) {
            $exchange_data[]    = $count;
        }
        
        $transactions   = Transaction::with(['currency'])->where("type",PaymentGatewayConst::BUY_CRYPTO)
                            ->orderBy('id','desc')->latest()->take(3)->get();
        $data                               = [
            'unverified_user'               => $unverified_user,
            'active_user'                   => $active_user,
            'user_percent'                  => $user_percent,
            'total_user_count'              => User::all()->count(),
            'user_chart_data'               => $user_chart,

            'active_ticket'                 => $active_ticket,
            'pending_ticket'                => $pending_ticket,
            'percent_ticket'                => $percent_ticket,
            'total_ticket_count'            => SupportTicket::all()->count(),

            'active_category'               => $active_category,
            'inactive_category'             => $inactive_category,
            'category_percent'              => $category_percent,
            'total_category_count'          => BlogCategory::all()->count(),

            'active_blog'                   => $active_blog,
            'inactive_blog'                 => $inactive_blog,
            'blog_percent'                  => $blog_percent,
            'total_blog_count'              => Blog::all()->count(),

            'pending_transactions'          => $pending_transactions,
            'confirm_transactions'          => $confirm_transactions,
            'percent_transactions'          => $percent_transactions,
            'total_transaction_count'       => Transaction::all()->count(),

            'pending_buy_crypto'            => $pending_buy_crypto,
            'confirm_buy_crypto'            => $confirm_buy_crypto,
            'percent_buy_crypto'            => $percent_buy_crypto,
            'total_buy_crypto_count'        => Transaction::where("type",PaymentGatewayConst::BUY_CRYPTO)->get()->count(),

            'total_charges'                 => $total_charges,
            'this_month_charge'             => $this_month_charge,
            'last_month_charge'             => $last_month_charge,
            
        ];
        return view('admin.sections.dashboard.index',compact(
            'page_title',
            'data',
            'transactions',
            'labels',
            'buy_data',
            'sell_data',
            'withdraw_data',
            'exchange_data'
        ));
    }


    /**
     * Logout Admin From Dashboard
     * @return view
     */
    public function logout(Request $request) {

        $push_notification_setting = BasicSettingsProvider::get()->push_notification_config;

        if($push_notification_setting) {
            $method = $push_notification_setting->method ?? false;

            if($method == "pusher") {
                $instant_id     = $push_notification_setting->instance_id ?? false;
                $primary_key    = $push_notification_setting->primary_key ?? false;

                if($instant_id && $primary_key) {
                    $pusher_instance = new PushNotifications([
                        "instanceId"    => $instant_id,
                        "secretKey"     => $primary_key,
                    ]);

                    $pusher_instance->deleteUser("".Auth::user()->id."");
                }
            }

        }

        $admin = auth()->user();
        try{
            $admin->update([
                'last_logged_out'   => now(),
                'login_status'      => false,
            ]);
        }catch(Exception $e) {
            // Handle Error
        }

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }


    /**
     * Function for clear admin notification
     */
    public function notificationsClear() {
        $admin = auth()->user();

        if(!$admin) {
            return false;
        }

        try{
            $admin->update([
                'notification_clear_at'     => now(),
            ]);
        }catch(Exception $e) {
            $error = ['error' => ['Something went wrong! Please try again.']];
            return Response::error($error,null,404);
        }

        $success = ['success' => ['Notifications clear successfully!']];
        return Response::success($success,null,200);
    }
}
