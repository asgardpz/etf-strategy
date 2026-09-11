<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EtfQuote extends Model
{
    protected $fillable = ['etf_id','price','reference_price','change_amount','change_percent','quote_time','raw_data'];
    protected $casts = ['price'=>'decimal:4','reference_price'=>'decimal:4','change_amount'=>'decimal:4','change_percent'=>'decimal:4','quote_time'=>'datetime','raw_data'=>'array'];
    public function etf(): BelongsTo { return $this->belongsTo(Etf::class); }
}
