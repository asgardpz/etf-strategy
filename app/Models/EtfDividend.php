<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EtfDividend extends Model
{
    protected $fillable = ['etf_id','ex_dividend_date','record_date','pay_date','dividend_amount','announcement_year','source_url','raw_data'];
    protected $casts = ['ex_dividend_date'=>'date','record_date'=>'date','pay_date'=>'date','dividend_amount'=>'decimal:4','raw_data'=>'array'];
    public function etf(): BelongsTo { return $this->belongsTo(Etf::class); }
}
