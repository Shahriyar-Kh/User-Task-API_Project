<?php
// tests/Feature/SimpleImportTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SimpleImportTest extends TestCase
{
    public function test_debug_import_process()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // Create a fake PDF file
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/import', ['file' => $file]);

        // Debug output
        echo "\n=== DEBUG INFO ===\n";
        echo "Status Code: " . $response->status() . "\n";
        echo "Response Body: " . $response->getContent() . "\n";
        echo "Headers: " . json_encode($response->headers->all()) . "\n";
        
        // Check what's in the database
        $records = \App\Models\ImportedRecord::all();
        echo "Records in DB: " . $records->count() . "\n";
        if ($records->count() > 0) {
            echo "Latest record: " . json_encode($records->last()->toArray()) . "\n";
        }
        echo "=== END DEBUG ===\n";

        // For now, just assert it doesn't crash
        $this->assertTrue(in_array($response->status(), [200, 201, 422]));
    }
}