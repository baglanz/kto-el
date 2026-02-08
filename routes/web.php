<?php

use App\Http\Controllers\BillSplitController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BillSplitController::class, 'index'])->name('bill.index');
Route::post('/calculate', [BillSplitController::class, 'calculate'])->name('bill.calculate');
Route::get('/download-pdf/{bill}', [BillSplitController::class, 'downloadPdf'])->name('bill.download-pdf');
