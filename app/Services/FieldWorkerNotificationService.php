<?php

namespace App\Services;

use App\Models\FieldWorker;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

/**
 * Sends Infobip SMS and WhatsApp notifications to a FieldWorker
 * when they are assigned to a task.
 *
 * FieldWorkers are NOT system users so this service bypasses
 * NotificationRouter and writes directly to InfobipClient.
 */
class FieldWorkerNotificationService
{
    /**
     * The Infobip WhatsApp template name.
     * Must be registered and approved in your Infobip dashboard before use.
     */
    public const WHATSAPP_TEMPLATE = 'field_worker_assignment';

    public function __construct(protected InfobipClient $client) {}

    /**
     * Notify a field worker that they have been assigned to a task.
     * Fires both SMS (immediate) and WhatsApp template (requires prior approval).
     */
    public function notifyAssigned(FieldWorker $worker, Task $task): void
    {
        $phone = $this->resolvePhone($worker);

        if (! $phone) {
            Log::info('FieldWorkerNotificationService: skipped (no phone number)', [
                'field_worker_id' => $worker->id,
                'task_id'         => $task->id,
            ]);
            return;
        }

        $this->sendSms($worker, $task, $phone);
        $this->sendWhatsApp($worker, $task, $phone);
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    /**
     * Resolve the E.164 phone number (digits only, no + prefix for Infobip SMS).
     */
    private function resolvePhone(FieldWorker $worker): ?string
    {
        $raw = $worker->phone_number;
        if (! $raw) {
            return null;
        }
        // Strip everything except digits; Infobip SMS endpoint wants digits only
        return preg_replace('/\D/', '', $raw) ?: null;
    }

    /**
     * Build a detailed plain-text SMS body and send it.
     */
    private function sendSms(FieldWorker $worker, Task $task, string $phone): void
    {
        $task->loadMissing('workOrder');

        $deadline    = $task->deadline ? $task->deadline->format('d M Y') : 'Not set';
        $jobCard     = $task->workOrder?->reference_number ?? 'N/A';
        $priority    = ucfirst($task->priority ?? 'normal');
        $description = $task->description
            ? mb_substr(strip_tags($task->description), 0, 320) . (mb_strlen(strip_tags($task->description)) > 320 ? '...' : '')
            : 'No additional details.';

        $text = <<<SMS
            TASK ASSIGNMENT – Household Media

            Hello {$worker->name},

            You have been assigned to a task. Please read carefully.

            Task   : {$task->title}
            Job Card: {$jobCard}
            Priority: {$priority}
            Deadline: {$deadline}

            Details:
            {$description}

            Report to your supervisor for further instructions.
            SMS;

        // Dedent the heredoc (PHP 7.3+ heredoc strips leading whitespace)
        $text = preg_replace('/^[ \t]+/m', '', $text);

        try {
            $this->client->sendSms('+' . $phone, $text);
            Log::info('FieldWorkerNotificationService: SMS sent', [
                'field_worker_id' => $worker->id,
                'task_id'         => $task->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('FieldWorkerNotificationService: SMS failed — ' . $e->getMessage(), [
                'field_worker_id' => $worker->id,
                'task_id'         => $task->id,
            ]);
        }
    }

    /**
     * Send the approved Infobip WhatsApp template message.
     * Silently skips if the template is not yet approved.
     */
    private function sendWhatsApp(FieldWorker $worker, Task $task, string $phone): void
    {
        $task->loadMissing('workOrder');

        $deadline    = $task->deadline ? $task->deadline->format('d M Y') : 'Not set';
        $jobCard     = $task->workOrder?->reference_number ?? 'N/A';
        $priority    = ucfirst($task->priority ?? 'normal');
        $description = $task->description
            ? mb_substr(strip_tags($task->description), 0, 200) . (mb_strlen(strip_tags($task->description)) > 200 ? '...' : '')
            : 'No additional details.';

        // Template placeholders — order matches the registered template body:
        // {{1}} name  {{2}} task  {{3}} job card  {{4}} priority  {{5}} deadline  {{6}} details
        $placeholders = [
            $worker->name,
            $task->title,
            $jobCard,
            $priority,
            $deadline,
            $description,
        ];

        // Resolve media header details for the MEDIA_TEMPLATE
        $mediaType = config('services.infobip.whatsapp_templates.field_worker_assignment.media_type', 'IMAGE');
        $mediaUrl  = config('services.infobip.whatsapp_templates.field_worker_assignment.fallback_url');

        if (! $mediaUrl) {
            // Default to brand logo for IMAGE, or leave null for DOCUMENT (requiring document attachment)
            $mediaUrl = $mediaType === 'IMAGE' ? asset('images/logo.png') : null;
        }

        // If the task has documents, use the first document matching the media type
        $document = $task->documents()->first();
        if ($document) {
            try {
                $isImage = $document->mime_type && str_starts_with($document->mime_type, 'image/');
                if (($mediaType === 'IMAGE' && $isImage) || ($mediaType === 'DOCUMENT' && ! $isImage)) {
                    $mediaUrl = \Illuminate\Support\Facades\Storage::disk('contabo')->temporaryUrl($document->file_path, now()->addHours(24));
                }
            } catch (\Throwable $e) {
                // Ignore and use default/fallback
            }
        }

        try {
            $this->client->sendWhatsAppTemplate(
                to:           $phone,
                templateName: self::WHATSAPP_TEMPLATE,
                placeholders: $placeholders,
                mediaUrl:     $mediaUrl,
                mediaType:    $mediaType,
            );
            Log::info('FieldWorkerNotificationService: WhatsApp sent', [
                'field_worker_id' => $worker->id,
                'task_id'         => $task->id,
                'media_url'       => $mediaUrl,
                'media_type'      => $mediaType,
            ]);
        } catch (\Throwable $e) {
            // WhatsApp may legitimately fail if the template is not yet approved —
            // log but do NOT rethrow so the SMS path is not affected.
            Log::warning('FieldWorkerNotificationService: WhatsApp failed (template may be pending approval) — ' . $e->getMessage(), [
                'field_worker_id' => $worker->id,
                'task_id'         => $task->id,
            ]);
        }
    }
}
