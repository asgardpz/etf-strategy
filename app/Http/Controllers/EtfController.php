<?php
namespace App\Http\Controllers;
use App\Models\Etf; use Illuminate\Http\JsonResponse;
class EtfController extends Controller { public function index(): JsonResponse {return response()->json(Etf::with('latestQuote')->where('is_active',true)->orderBy('code')->get());} public function show(string $code): JsonResponse { $etf=Etf::with(['latestQuote','dividends'=>fn($q)=>$q->orderByDesc('ex_dividend_date')->limit(12)])->where('code',$code)->firstOrFail(); return response()->json($etf); } }
