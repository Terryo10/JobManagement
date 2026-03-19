<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AiReportService;
use Filament\Widgets\Widget;

class AiAssistantWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.ai-assistant-widget';
    protected static ?int $sort = -1;
    protected int | string | array $columnSpan = 1;

    public string $selectedTopic = 'work_orders';
    public bool $isModalOpen = false;
    public string $summary = '';
    public array $messages = [];
    public string $messageInput = '';
    public array $contextData = [];

    public static function getTopics(): array
    {
        return [
            'work_orders' => ['label' => 'Work Orders', 'icon' => 'heroicon-o-clipboard-document-list'],
            'finance'     => ['label' => 'Finance',     'icon' => 'heroicon-o-banknotes'],
            'staff'       => ['label' => 'Staff',       'icon' => 'heroicon-o-users'],
            'crm'         => ['label' => 'CRM & Leads', 'icon' => 'heroicon-o-user-group'],
            'inventory'   => ['label' => 'Inventory',   'icon' => 'heroicon-o-archive-box'],
        ];
    }

    public function selectTopic(string $topic): void
    {
        $this->selectedTopic = $topic;
        $this->summary       = '';
        $this->messages      = [];
        $this->contextData   = [];
    }

    public function getSummary(): void
    {
        $service = new AiReportService();

        [$this->contextData, $this->summary] = $service->getTopicSummaryWithContext($this->selectedTopic);

        $this->messages    = [];
        $this->isModalOpen = true;
    }

    public function sendMessage(): void
    {
        $text = trim($this->messageInput);
        if ($text === '') {
            return;
        }

        $this->messageInput = '';
        $this->messages[]   = ['role' => 'user', 'content' => $text];

        $service            = new AiReportService();
        $response           = $service->chatAboutTopic($this->selectedTopic, $this->contextData, $this->messages);
        $this->messages[]   = ['role' => 'assistant', 'content' => $response];
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
    }
}
