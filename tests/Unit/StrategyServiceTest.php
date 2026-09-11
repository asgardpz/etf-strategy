<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase; use App\Services\StrategyService;
class StrategyServiceTest extends TestCase { public function test_previous_trading_day_skips_weekend(): void { $d=(new StrategyService)->previousTradingDay(\Carbon\Carbon::parse('2026-09-14')); $this->assertSame('2026-09-11',$d->toDateString()); } }
