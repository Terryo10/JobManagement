<?php

namespace App\Jobs;

use App\Models\FieldWorker;
use App\Models\Task;
use App\Services\FieldWorkerNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job that delivers an assignment notification (SMS + WhatsApp)
 * to a FieldWorker via Infobip.
 *
 * Uses integer IDs rather than Eloquent models to keep the
 * serialized job payload small and database-safe.
 */
class SendFieldWorkerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Number of times the job may be attempted before it is marked as failed. */
    public int $tries = 3;

    /** Seconds to wait between retry attempts. */
    public int $backoff = 60;

    public function __construct(
        public readonly int $fieldWorkerId,
        public readonly int $taskId,
    ) {}

    public function handle(FieldWorkerNotificationService $service): void
    {
        $worker = FieldWorker::find($this->fieldWorkerId);
        $task   = Task::find($this->taskId);

        if (! $worker || ! $task) {
            Log::warning('SendFieldWorkerNotificationJob: record not found, skipping.', [
                'field_worker_id' => $this->fieldWorkerId,
                'task_id'         => $this->taskId,
            ]);
            return;
        }

        $service->notifyAssigned($worker, $task);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendFieldWorkerNotificationJob permanently failed after retries.', [
            'field_worker_id' => $this->fieldWorkerId,
            'task_id'         => $this->taskId,
            'error'           => $e->getMessage(),
        ]);
    }
}
