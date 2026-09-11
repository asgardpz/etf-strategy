<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StrategyResult extends Model
{
    protected $fillable = ['etf_id','account_amount','quote_price','shares','lots','investment_amount','estimated_dividend','capital_gain','total_return','return_rate','signal','analysis_date','ex_dividend_date','pay_date','last_buy_date','dividend_received','purchase_price','notes'];
    protected $casts = ['account_amount'=>'decimal:2','quote_price'=>'decimal:4','investment_amount'=>'decimal:2','estimated_dividend'=>'decimal:2','capital_gain'=>'decimal:2','total_return'=>'decimal:2','return_rate'=>'decimal:4','analysis_date'=>'date','ex_dividend_date'=>'date','pay_date'=>'date','last_buy_date'=>'date','dividend_received'=>'boolean','purchase_price'=>'decimal:4'];
    public function etf(): BelongsTo { return $this->belongsTo(Etf::class); }
}
