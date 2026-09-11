<?php
namespace App\Services;
use App\Models\Etf; use App\Models\Portfolio; use App\Models\StrategyResult; use Carbon\Carbon;
class StrategyService
{
    public function analyzeEtf(Etf $etf,float $accountAmount,?Carbon $today=null): ?StrategyResult
    {
        $today=$today?->copy()??today(); $dividend=$etf->dividends()->whereDate('ex_dividend_date','>=',$today->toDateString())->orderBy('ex_dividend_date')->first(); $quote=$etf->latestQuote; if(!$dividend||!$quote||!$quote->price)return null;
        $price=(float)$quote->price; $lots=(int)floor($accountAmount/($price*1000)); $shares=$lots*1000; $investment=$shares*$price; $estimated=$shares*(float)($dividend->dividend_amount??0); $lastBuy=$this->previousTradingDay(Carbon::parse($dividend->ex_dividend_date));
        $signal=$today->lte($lastBuy)&&$shares>0?'BUY_CANDIDATE':'WAIT'; $portfolio=Portfolio::where('etf_id',$etf->id)->where('status','holding')->first(); $capitalGain=null;$totalReturn=null;$returnRate=null;$notes=null;
        if($portfolio){$gain=($price-(float)$portfolio->purchase_price)*$portfolio->shares;$received=$portfolio->dividend_received?(float)$portfolio->dividend_received_amount:0;$capitalGain=$gain;$totalReturn=$gain+$received;$returnRate=$portfolio->purchase_price>0?($totalReturn/((float)$portfolio->purchase_price*$portfolio->shares))*100:0;if($portfolio->dividend_received&&$gain>$received){$signal='SELL_ALERT';$notes='持有部位股價價差已超過已領股利。';}elseif($portfolio->dividend_received){$signal='HOLD';}}
        return $this->saveResult($etf,$accountAmount,$price,$shares,$lots,$investment,$estimated,$signal,$dividend,$lastBuy,$capitalGain,$totalReturn,$returnRate,$portfolio,$notes,$today);
    }
    public function analyzePortfolio(Portfolio $portfolio,float $accountAmount,?Carbon $today=null): ?StrategyResult
    {
        $today=$today?->copy()??today(); $etf=$portfolio->etf()->with('latestQuote')->first(); if(!$etf||!$etf->latestQuote||!$etf->latestQuote->price)return null; $dividend=$etf->dividends()->whereDate('ex_dividend_date','<=',$today->toDateString())->orderByDesc('ex_dividend_date')->first(); if(!$dividend)return null;
        $price=(float)$etf->latestQuote->price; $gain=($price-(float)$portfolio->purchase_price)*$portfolio->shares; $received=$portfolio->dividend_received?(float)$portfolio->dividend_received_amount:0; $total=$gain+$received; $rate=((float)$portfolio->purchase_price>0)?$total/((float)$portfolio->purchase_price*$portfolio->shares)*100:0; $signal=$portfolio->dividend_received&&$gain>$received?'SELL_ALERT':'HOLD'; $notes=$signal==='SELL_ALERT'?'持有部位股價價差已超過已領股利。':null;
        return $this->saveResult($etf,$accountAmount,$price,$portfolio->shares,intdiv($portfolio->shares,1000),$portfolio->shares*$price,0,$signal,$dividend,$this->previousTradingDay(Carbon::parse($dividend->ex_dividend_date)),$gain,$total,$rate,$portfolio,$notes,$today);
    }
    private function saveResult(Etf $etf,float $accountAmount,float $price,int $shares,int $lots,float $investment,float $estimated,string $signal,$dividend,Carbon $lastBuy,?float $capitalGain,?float $totalReturn,?float $returnRate,?Portfolio $portfolio=null,?string $notes=null,Carbon $today=null): StrategyResult { return StrategyResult::updateOrCreate(['etf_id'=>$etf->id,'analysis_date'=>($today??today())->toDateString()],['account_amount'=>$accountAmount,'quote_price'=>$price,'shares'=>$shares,'lots'=>$lots,'investment_amount'=>$investment,'estimated_dividend'=>$estimated,'capital_gain'=>$capitalGain,'total_return'=>$totalReturn,'return_rate'=>$returnRate,'signal'=>$signal,'ex_dividend_date'=>$dividend->ex_dividend_date,'pay_date'=>$dividend->pay_date,'last_buy_date'=>$lastBuy->toDateString(),'dividend_received'=>$portfolio?->dividend_received??false,'purchase_price'=>$portfolio?->purchase_price,'notes'=>$notes]); }
    public function previousTradingDay(Carbon $date): Carbon { $d=$date->copy()->subDay(); while($d->isWeekend())$d->subDay(); return $d; }
    public function analyzeAll(float $accountAmount): int { $count=0; $ids=[]; Etf::where('is_active',true)->whereHas('dividends',fn($q)=>$q->whereDate('ex_dividend_date','>=',today()))->with('latestQuote')->chunkById(50,function($etfs)use($accountAmount,&$count,&$ids){foreach($etfs as $etf){$ids[]=$etf->id;if($this->analyzeEtf($etf,$accountAmount))$count++;}}); Portfolio::where('status','holding')->with('etf')->chunkById(50,function($items)use($accountAmount,&$count){foreach($items as $p){if($this->analyzePortfolio($p,$accountAmount))$count++;}}); return $count; }
}
