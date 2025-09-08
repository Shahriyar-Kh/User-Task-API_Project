<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// routes/web.php
use App\Http\Controllers\ImportController;

Route::get('/import-form', function () {
    return view('import-form');
});


Route::get('/test-ocr', function() {
    // Test pdftotext
    $pdfOutput = shell_exec(config('pdf.pdftotext') . ' -v 2>&1');

    // Test tesseract
    $ocrOutput = shell_exec(config('ocr.tesseract') . ' -v 2>&1');

    return response()->json([
        'pdftotext' => $pdfOutput,
        'tesseract' => $ocrOutput
    ]);
});
