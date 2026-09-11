<?php

namespace App\Http\Controllers;

use App\Models\StrategyResult;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    public function index()
    {
        $account = (float) AppSetting::getValue('account_amount', config('etf.account_amount'));
        return view('dashboard', ['accountAmount' => $account]);
    }

    public function data()
    {
        try {
            $results = StrategyResult::with([
                    'etf',
                    'etf.dividends' => fn($q) =>
                        $q->whereDate('ex_dividend_date', '>=', today())
                        ->orderBy('ex_dividend_date')
                ])
                ->join('etfs', 'strategy_results.etf_id', '=', 'etfs.id')
                ->where('analysis_date', now()->toDateString())
                ->where('estimated_dividend', '>', 0)
                ->where('etfs.dividend_frequency', 'quarterly') // 只抓季配息
                ->orderBy('last_buy_date', 'asc')
                ->orderByRaw("FIELD(`signal`,'SELL_ALERT','BUY_CANDIDATE','HOLD','WAIT')")
                ->orderByDesc('estimated_dividend')
                ->get(['strategy_results.*']); // 避免欄位衝突


            return response()->json([
                'updated_at' => now()->toIso8601String(),
                'account_amount' => (float) AppSetting::getValue('account_amount', config('etf.account_amount')),
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function refresh(Request $request)
    {
        $amount = max(0, (float) $request->input('account_amount', config('etf.account_amount')));
        AppSetting::setValue('account_amount', $amount);

        Artisan::call('etf:fetch', ['--account' => (string) $amount]);

        return response()->json(['ok' => true, 'message' => '資料更新完成']);
    }
}
