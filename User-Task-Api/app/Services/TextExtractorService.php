<?php

namespace App\Services;

use Spatie\PdfToText\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;

class TextExtractorService
{
    /**
     * Extract text from PDF or Image
     */
    public function extract(string $absolutePath, string $extension): string
    {
        $ext = strtolower($extension);

        if (!file_exists($absolutePath)) {
            Log::error("File not found for extraction: $absolutePath");
            return '';
        }

        try {
            if ($ext === 'pdf') {
                // PDF extraction
                $text = Pdf::getText($absolutePath, config('pdf.pdftotext'));
                Log::info("PDF extracted text length: " . mb_strlen($text));

                // If PDF text is almost empty, fallback to OCR
                if ($this->isMostlyEmpty($text)) {
                    return $this->ocr($absolutePath);
                }

                return $text ?: $this->ocr($absolutePath);
            }

            // For images or unknown extensions
            return $this->ocr($absolutePath);

        } catch (\Throwable $e) {
            Log::error('Extraction error: ' . $e->getMessage());
            return '';
        }
    }

    private function ocr(string $path): string
    {
        try {
            $ocr = new TesseractOCR($path);
            if (config('ocr.tesseract')) $ocr->executable(config('ocr.tesseract'));
            if (config('ocr.lang')) $ocr->lang(config('ocr.lang'));

            $text = $ocr->run();
            Log::info("OCR extracted text length: " . mb_strlen($text));
            return $text;

        } catch (\Throwable $e) {
            Log::error('OCR error: ' . $e->getMessage());
            return '';
        }
    }

    private function isMostlyEmpty(?string $text): bool
    {
        return !$text || mb_strlen(trim(preg_replace('/\s+/', '', $text))) < 10;
    }
}
