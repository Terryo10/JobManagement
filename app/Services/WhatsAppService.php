<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct(protected InfobipClient $client) {}

    /**
     * Send a Welcome/Onboarding template message.
     */
    public function sendWelcomeNotification(string $to, string $name): bool
    {
        try {
            // Using a generic template name. This should be updated to the exact Meta-approved name.
            $this->client->sendWhatsAppTemplate($to, 'welcome_onboarding', [$name]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Welcome failed: ' . $e->getMessage(), [
                'to' => $to,
                'name' => $name,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Send an Alert/Action Required template message.
     */
    public function sendActionRequiredAlert(string $to, string $taskId, string $taskName): bool
    {
        try {
            $this->client->sendWhatsAppTemplate($to, 'action_required_alert', [$taskId, $taskName]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Action Alert failed: ' . $e->getMessage(), [
                'to' => $to,
                'task_id' => $taskId,
                'task_name' => $taskName,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Send a Completion/Success template message.
     */
    public function sendCompletionSuccess(string $to, string $taskId): bool
    {
        try {
            $this->client->sendWhatsAppTemplate($to, 'completion_success', [$taskId]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Completion Success failed: ' . $e->getMessage(), [
                'to' => $to,
                'task_id' => $taskId,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
