<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\TextExtractorService;
use App\Services\FieldParserService;
use App\Models\ImportedRecord;

class ImportController extends Controller
{
    protected $textExtractor;
    protected $fieldParser;

    public function __construct(TextExtractorService $textExtractor, FieldParserService $fieldParser)
    {
        $this->textExtractor = $textExtractor;
        $this->fieldParser = $fieldParser;
    }

    /**
     * Handle file upload and store parsed fields
     */
    public function upload(Request $request)
    {
        // Validate file
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . $file->getClientOriginalName();

        // Save file to storage/app/uploads
        $path = $file->storeAs('uploads', $filename);

        // Extract text from file
        $absolutePath = storage_path('app/' . $path);
        $extractedText = $this->textExtractor->extract($absolutePath, $extension);

        // Parse fields from extracted text
        $parsedFields = $this->fieldParser->parse($extractedText);

        // Store in database
        $importedRecord = ImportedRecord::create([
            'filename'   => $filename,
            'name'       => $parsedFields['name'] ?? 'Unknown',
            'email'      => $parsedFields['email'] ?? null,
            'phone'      => $parsedFields['phone'] ?? null,
            'address'    => $parsedFields['address'] ?? null,
            'city'       => $parsedFields['city'] ?? null,
            'state'      => $parsedFields['state'] ?? null,
            'zip'        => $parsedFields['zip'] ?? null,
            'dob'        => $parsedFields['dob'] ?? null,
            'gender'     => $parsedFields['gender'] ?? null,
            'occupation' => $parsedFields['occupation'] ?? null,
            'user_id'    => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded and data parsed successfully',
            'data'    => $importedRecord,
        ]);
    }
}
