<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Portfolio extends Model
{
    protected $fillable = ['etf_id','shares','purchase_price','purchase_date','dividend_received','dividend_received_amount','status','notes'];
    protected $casts = ['shares'=>'integer','purchase_price'=>'decimal:4','purchase_date'=>'date','dividend_received'=>'boolean','dividend_received_amount'=>'decimal:2'];
    public function etf(): BelongsTo { return $this->belongsTo(Etf::class); }
}
