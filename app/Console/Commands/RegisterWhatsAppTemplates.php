<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RegisterWhatsAppTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infobip:whatsapp-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register required WhatsApp templates with Infobip';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = rtrim(config('services.infobip.base_url'), '/');
        $apiKey = config('services.infobip.api_key');
        $sender = config('services.infobip.whatsapp_sender');

        if (!$baseUrl || !$apiKey || !$sender) {
            $this->error("Infobip base URL, API key, and WhatsApp Sender must be configured.");
            $this->error("Please add INFOBIP_BASE_URL, INFOBIP_API_KEY, and INFOBIP_WHATSAPP_SENDER to your .env file.");
            return Command::FAILURE;
        }

        $templates = [
            [
                'name' => 'welcome_onboarding',
                'language' => 'en',
                'category' => 'UTILITY',
                'allowCategoryChange' => true,
                'structure' => [
                    'body' => [
                        'text' => "Hello {{1}}! Welcome to our platform. Let us know if you have any questions."
                    ]
                ]
            ],
            [
                'name' => 'action_required_alert',
                'language' => 'en',
                'category' => 'UTILITY',
                'allowCategoryChange' => true,
                'structure' => [
                    'body' => [
                        'text' => "Action Required: Task #{{1}} ({{2}}) needs your attention."
                    ]
                ]
            ],
            [
                'name' => 'completion_success',
                'language' => 'en',
                'category' => 'UTILITY',
                'allowCategoryChange' => true,
                'structure' => [
                    'body' => [
                        'text' => "Success! Task #{{1}} has been marked as completed."
                    ]
                ]
            ]
        ];

        foreach ($templates as $template) {
            $this->info("Submitting template: {$template['name']}...");

            $response = Http::withHeaders([
                'Authorization' => "App $apiKey",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("$baseUrl/whatsapp/2/senders/$sender/templates", $template);

            if ($response->successful()) {
                $this->info("Successfully submitted {$template['name']}.");
            } else {
                $this->error("Failed to submit {$template['name']}.");
                $this->error($response->body());
            }
        }

        $this->newLine();
        $this->info("Templates have been submitted to Infobip. They will require Meta's approval before they can be used.");

        return Command::SUCCESS;
    }
}
