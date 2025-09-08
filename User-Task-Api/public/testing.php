<?php
// Test pdftotext
$output = shell_exec(config('pdf.pdftotext') . ' -v 2>&1');
dd($output);

// Test tesseract
$output = shell_exec(config('ocr.tesseract') . ' -v 2>&1');
dd($output);
