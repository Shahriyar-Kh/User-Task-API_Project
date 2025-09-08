<?php
// tests/Feature/ImportApiTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Models\ImportedRecord;
use App\Services\TextExtractorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're using the test database
        config(['database.connections.mysql.database' => 'user_task_db_test']);
    }

    public function test_upload_pdf_with_mocked_text_extraction()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // Mock the TextExtractorService to return structured text
        $this->mock(TextExtractorService::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn($this->getSampleStructuredText());
        });

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/import', ['file' => $file]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'stored_path',
            'record'
        ]);

        // Verify database record
        $this->assertDatabaseHas('imported_records', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'filename' => 'sample.pdf',
            'user_id' => $user->id
        ]);
    }

    public function test_upload_with_empty_text_extraction()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // Mock empty text extraction
        $this->mock(TextExtractorService::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn('');
        });

        $file = UploadedFile::fake()->create('empty.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/import', ['file' => $file]);

        // Should still work in testing environment
        $response->assertStatus(201);
        
        // Should create record with null values
        $this->assertDatabaseHas('imported_records', [
            'filename' => 'empty.pdf',
            'user_id' => $user->id,
            'name' => null,
            'email' => null
        ]);
    }

    public function test_upload_with_partial_data()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // Mock partial text extraction
        $this->mock(TextExtractorService::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn("Name: Jane Smith\nEmail: jane@test.com");
        });

        $file = UploadedFile::fake()->create('partial.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/import', ['file' => $file]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('imported_records', [
            'name' => 'Jane Smith',
            'email' => 'jane@test.com',
            'phone' => null, // Should be null for missing fields
            'user_id' => $user->id
        ]);
    }

    public function test_upload_requires_valid_file()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/import', ['file' => 'not-a-file']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_requires_authentication()
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->create('sample.pdf', 10, 'application/pdf');

        $response = $this->postJson('/api/import', ['file' => $file]);

        $response->assertStatus(401);
    }

    private function getSampleStructuredText(): string
    {
        return "
Name: John Doe
Email: john@example.com
Phone: +1 (555) 123-4567
Address: 123 Main Street
City: New York
State: NY
Zip: 10001
DOB: 15/01/1990
Gender: Male
Occupation: Software Engineer
";
    }
}