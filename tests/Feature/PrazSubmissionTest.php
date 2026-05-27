<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\PrazSubmissionResource\Pages\CreatePrazSubmission;
use App\Models\PrazSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PrazSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_praz_submission_with_attachments()
    {
        Storage::fake('contabo');

        $user = User::factory()->create();

        // Create a fake file
        $file = UploadedFile::fake()->create('tender-document.pdf', 100);
        $storedPath = Storage::disk('contabo')->putFile('documents/praz-submissions', $file);

        Livewire::actingAs($user)
            ->test(CreatePrazSubmission::class)
            ->fillForm([
                'title' => 'Billboard Tender – Harare CBD',
                'tender_number' => 'PRAZ/2026/GOV/001',
                'category' => 'services',
                'procuring_entity' => 'Ministry of Information',
                'submission_deadline' => now()->addDays(7)->toDateTimeString(),
                'status' => 'draft',
                'attachments' => [$storedPath],
                'attachment_names' => ['tender-document.pdf'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $submission = PrazSubmission::first();
        $this->assertNotNull($submission);
        $this->assertEquals('Billboard Tender – Harare CBD', $submission->title);
        $this->assertEquals($user->id, $submission->prepared_by);

        $document = $submission->documents()->first();
        $this->assertNotNull($document);
        $this->assertEquals('tender-document.pdf', $document->name);
        $this->assertEquals($storedPath, $document->file_path);
        $this->assertEquals('pdf', $document->mime_type);
        $this->assertEquals($user->id, $document->uploaded_by);
    }
}
