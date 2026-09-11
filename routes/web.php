<?php
use Illuminate\Support\Facades\Route; use App\Http\Controllers\DashboardController;
Route::get('/',[DashboardController::class,'index'])->name('dashboard');
Route::get('/dashboard/data',[DashboardController::class,'data'])->name('dashboard.data');
Route::post('/dashboard/refresh',[DashboardController::class,'refresh'])->name('dashboard.refresh');
