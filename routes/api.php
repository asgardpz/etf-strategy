<?php
use Illuminate\Support\Facades\Route; use App\Http\Controllers\EtfController;
Route::get('/etfs',[EtfController::class,'index']); Route::get('/etfs/{code}',[EtfController::class,'show']);
