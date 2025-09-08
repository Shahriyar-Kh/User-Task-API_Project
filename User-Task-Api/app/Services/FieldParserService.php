<?php

namespace App\Services;

class FieldParserService
{
    /**
     * Parse structured fields from extracted text
     */
    public function parse(string $text): array
    {
        // Normalize text: replace newlines and multiple spaces
        $text = preg_replace("/\r\n|\r|\n/", "\n", $text);
        $text = preg_replace("/\s+/", " ", $text);

        $fields = [
            'name'       => $this->match('/(Name|Full Name)\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'email'      => $this->match('/(Email|E-?mail)\s*[:\-]?\s*([^\s]+)/i', $text),
            'phone'      => $this->match('/(Phone|Phone Number|Mobile)\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'address'    => $this->match('/Address\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'city'       => $this->match('/City\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'state'      => $this->match('/State\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'zip'        => $this->match('/(Zip|Zip Code|Postal Code)\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'dob'        => $this->match('/(DOB|Date of Birth)\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'gender'     => $this->match('/Gender\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
            'occupation' => $this->match('/Occupation\s*[:\-]?\s*(.+?)(?=\s{2,}|$)/i', $text),
        ];

        // If no name found, set Unknown as fallback
        if (empty($fields['name'])) {
            $fields['name'] = 'Unknown';
        }

        return $fields;
    }

    /**
     * Regex helper to match first group or return null
     */
    private function match(string $pattern, string $text): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[2] ?? $matches[1]);
        }
        return null;
    }
}
