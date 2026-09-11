<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Etf extends Model
{
    protected $fillable = ['code','name','market','is_active','last_synced_at'];
    protected $casts = ['is_active'=>'boolean','last_synced_at'=>'datetime'];
    public function dividends(): HasMany { return $this->hasMany(EtfDividend::class); }
    public function quotes(): HasMany { return $this->hasMany(EtfQuote::class); }
    public function portfolios(): HasMany { return $this->hasMany(Portfolio::class); }
    public function latestQuote(): HasOne { return $this->hasOne(EtfQuote::class)->latestOfMany('quote_time'); }
}
