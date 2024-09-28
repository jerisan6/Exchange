<?php
namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{   
    /**
     * Method for dashboard index page
     */
    public function index()
    {
        $page_title     = "- Dashboard";
        $wallets           = UserWallet::auth()->with(['currency'])->get();
        $monthlyDataBuy    = Transaction::auth()->where("type",PaymentGatewayConst::BUY_CRYPTO)->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        $monthlyDataSell    = Transaction::auth()->where("type",PaymentGatewayConst::SELL_CRYPTO)->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        $monthlyDataWithdraw    = Transaction::auth()->where("type",PaymentGatewayConst::WITHDRAW_CRYPTO)->select(
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
        $data           = [];
        $sell_data      = [];
        $withdraw_data  = [];

        
        
        // Create an array with all months 
        $monthsArray            = array_fill_keys(range(1, 12), 0);
        $monthsArraySell        = array_fill_keys(range(1, 12), 0);
        $monthsArrayWithdraw    = array_fill_keys(range(1, 12), 0);
        
        
        foreach ($monthlyDataBuy as $record) {
            $monthsArray[$record->month] = $record->total;
        }

        foreach ($monthlyDataSell as $record) {
            $monthsArraySell[$record->month] = $record->total;
        }

        foreach ($monthlyDataWithdraw as $record) {
            $monthsArrayWithdraw[$record->month] = $record->total;
        }
        
        foreach ($monthsArray as $month => $count) {
            $monthName = date('M', mktime(0, 0, 0, $month, 1)); // Full month name
            $labels[] = $monthName;
            $data[] = $count;
        }

        foreach ($monthsArraySell as $month => $count) {
            $sell_data[]    = $count;
        }

        foreach ($monthsArrayWithdraw as $month => $count) {
            $withdraw_data[]    = $count;
        }
        
        $transactions   = Transaction::auth()->where("type",PaymentGatewayConst::BUY_CRYPTO)
        ->orderBy('id','desc')->latest()->take(3)->get();
        
        return view('user.dashboard',compact(
            "page_title",
            "wallets",
            'transactions',
            'labels',
            'data',
            'sell_data',
            'withdraw_data'
        ));
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('user.login');
    }
    //Method for delete profile
    public function deleteAccount(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required',
        ]);
        $validated = $validator->validate();
        $user = auth()->user();
        try{
            $user->status = 0;
            $user->save();
            Auth::logout();
            return redirect()->route('index')->with(['success' => ['Your account deleted successfully!']]);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }
    }
}
