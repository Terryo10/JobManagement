<?php

namespace App\Services;

use App\Models\AdminTask;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\StockLevel;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Proposal;
use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiReportService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl;

    private const TOPIC_LABELS = [
        'work_orders'  => 'Work Orders & Job Operations',
        'finance'      => 'Finance & Invoices',
        'staff'        => 'Staff Activity & Performance',
        'crm'          => 'CRM, Leads & Proposals',
        'inventory'    => 'Inventory & Stock Levels',
        'admin_tasks'  => 'Admin Tasks & Internal Actions',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    // ─── AI Assistant Widget ───────────────────────────────────────────────────

    /**
     * Fetch live data for a topic and generate a Gemini summary.
     * Returns [contextData, summaryMarkdown].
     */
    public function getTopicSummaryWithContext(string $topic): array
    {
        $data    = $this->fetchTopicData($topic);
        $summary = $this->generateTopicSummary($topic, $data);

        return [$data, $summary];
    }

    /**
     * Continue a multi-turn chat about a topic using previously fetched context.
     * $messages is the full history including the latest user message.
     */
    public function chatAboutTopic(string $topic, array $contextData, array $messages): string
    {
        $label    = self::TOPIC_LABELS[$topic] ?? $topic;
        $dataJson = json_encode($contextData, JSON_PRETTY_PRINT);

        $systemContext = <<<PROMPT
You are a smart business assistant for Household Media. You have access to the following live {$label} data pulled right now. Answer questions concisely, helpfully, and based on this data.

## Current Live Data

```json
{$dataJson}
```
PROMPT;

        $contents = [
            ['role' => 'user',  'parts' => [['text' => $systemContext . "\n\nAcknowledge you have this data and are ready to help."]]],
            ['role' => 'model', 'parts' => [['text' => "Got it — I have the latest {$label} data loaded and I'm ready to answer your questions."]]],
        ];

        $history = array_slice($messages, 0, -1);
        foreach ($history as $msg) {
            $contents[] = [
                'role'  => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $last      = end($messages);
        $contents[] = ['role' => 'user', 'parts' => [['text' => $last['content']]]];

        return $this->callGeminiMultiTurn($contents);
    }

    // ─── Private: data fetchers ────────────────────────────────────────────────

    private function fetchTopicData(string $topic): array
    {
        return match ($topic) {
            'work_orders' => $this->fetchWorkOrderData(),
            'finance'     => $this->fetchFinanceData(),
            'staff'       => $this->fetchStaffData(),
            'crm'         => $this->fetchCrmData(),
            'inventory'   => $this->fetchInventoryData(),
            'admin_tasks' => $this->fetchAdminTaskData(),
            default       => [],
        };
    }

    private function fetchWorkOrderData(): array
    {
        $now = now();

        $byStatus = WorkOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $overdue = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', $now)
            ->count();

        $urgent = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->where('priority', 'urgent')
            ->count();

        $recent = WorkOrder::with([
                'client:id,company_name',
                'tasks' => fn ($q) => $q->whereNotIn('status', ['cancelled'])
                    ->with('assignedTo:id,name')
                    ->select('id', 'work_order_id', 'title', 'status', 'priority', 'deadline', 'assigned_to', 'completion_percentage'),
            ])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn ($wo) => [
                'ref'         => $wo->reference_number,
                'title'       => $wo->title,
                'client'      => $wo->client?->company_name,
                'status'      => $wo->status,
                'priority'    => $wo->priority,
                'category'    => $wo->category,
                'deadline'    => $wo->deadline?->format('Y-m-d'),
                'budget'      => $wo->budget,
                'actual_cost' => $wo->actual_cost,
                'tasks'       => $wo->tasks->map(fn ($t) => [
                    'title'          => $t->title,
                    'status'         => $t->status,
                    'priority'       => $t->priority,
                    'assigned_to'    => $t->assignedTo?->name ?? 'Unassigned',
                    'deadline'       => $t->deadline?->format('Y-m-d'),
                    'completion_pct' => $t->completion_percentage,
                ])->toArray(),
            ])->toArray();

        return [
            'as_of'             => $now->toDateTimeString(),
            'totals_by_status'  => $byStatus,
            'overdue_count'     => $overdue,
            'urgent_count'      => $urgent,
            'recent_work_orders'=> $recent,
        ];
    }

    private function fetchFinanceData(): array
    {
        $now = now();

        $byStatus = Invoice::selectRaw('status, count(*) as count, sum(total) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->status => ['count' => $r->count, 'total' => (float) $r->total]])
            ->toArray();

        $outstanding = Invoice::whereIn('status', ['sent', 'signed', 'approved', 'overdue'])->sum('total');
        $paidThisMonth = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now])
            ->sum('total');

        $overdue = Invoice::where('status', 'overdue')
            ->orWhere(fn ($q) => $q->whereIn('status', ['sent', 'approved'])->where('due_at', '<', $now))
            ->count();

        $recent = Invoice::with('client:id,company_name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($inv) => [
                'number'  => $inv->invoice_number,
                'client'  => $inv->client?->company_name,
                'status'  => $inv->status,
                'total'   => (float) $inv->total,
                'due_at'  => $inv->due_at?->format('Y-m-d'),
                'paid_at' => $inv->paid_at?->format('Y-m-d'),
            ])->toArray();

        return [
            'as_of'             => $now->toDateTimeString(),
            'invoices_by_status'=> $byStatus,
            'outstanding_total' => (float) $outstanding,
            'paid_this_month'   => (float) $paidThisMonth,
            'overdue_count'     => $overdue,
            'recent_invoices'   => $recent,
        ];
    }

    private function fetchStaffData(): array
    {
        $now = now();

        // All active staff
        $activeStaff = User::where('is_active', true)
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        // All open tasks with full detail
        $openTasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->with([
                'assignedTo:id,name',
                'workOrder:id,reference_number,title',
            ])
            ->orderBy('deadline')
            ->get([
                'id', 'title', 'status', 'priority',
                'deadline', 'assigned_to',
                'work_order_id', 'completion_percentage',
            ]);

        // Tasks completed this week (with who completed them)
        $completedThisWeek = Task::where('status', 'completed')
            ->where('updated_at', '>=', $now->copy()->startOfWeek())
            ->with('assignedTo:id,name', 'workOrder:id,reference_number')
            ->get(['id', 'title', 'assigned_to', 'work_order_id', 'updated_at'])
            ->map(fn ($t) => [
                'title'      => $t->title,
                'staff'      => $t->assignedTo?->name ?? 'Unassigned',
                'work_order' => $t->workOrder?->reference_number,
                'completed'  => $t->updated_at->format('Y-m-d'),
            ])->values()->toArray();

        // Group open tasks by person
        $tasksByPerson = [];
        $unassignedTasks = [];

        foreach ($openTasks as $task) {
            $isOverdue = $task->deadline && $task->deadline->isPast();
            $detail = [
                'task'               => $task->title,
                'status'             => $task->status,
                'priority'           => $task->priority,
                'deadline'           => $task->deadline?->format('Y-m-d'),
                'overdue'            => $isOverdue,
                'work_order'         => $task->workOrder?->reference_number ?? null,
                'work_order_title'   => $task->workOrder?->title ?? null,
                'completion_pct'     => $task->completion_percentage,
            ];

            if ($task->assigned_to && isset($activeStaff[$task->assigned_to])) {
                $name = $activeStaff[$task->assigned_to]->name;
                $tasksByPerson[$name][] = $detail;
            } else {
                $unassignedTasks[] = $detail;
            }
        }

        // Staff with zero open tasks
        $assignedIds = $openTasks->whereNotNull('assigned_to')->pluck('assigned_to')->unique();
        $idleStaff = $activeStaff
            ->whereNotIn('id', $assignedIds)
            ->pluck('name')
            ->values()
            ->toArray();

        // Summary counts
        $tasksByStatus = $openTasks->groupBy('status')
            ->map(fn ($g) => $g->count())
            ->toArray();

        $overdueCount = $openTasks->filter(
            fn ($t) => $t->deadline && $t->deadline->isPast()
        )->count();

        return [
            'as_of'                  => $now->toDateTimeString(),
            'active_staff_count'     => $activeStaff->count(),
            'open_tasks_by_status'   => $tasksByStatus,
            'overdue_task_count'     => $overdueCount,
            'completed_this_week'    => $completedThisWeek,
            'tasks_per_person'       => $tasksByPerson,
            'unassigned_tasks'       => $unassignedTasks,
            'idle_staff_no_tasks'    => $idleStaff,
        ];
    }

    private function fetchCrmData(): array
    {
        $now = now();

        $leadsByStatus = Lead::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $newThisMonth = Lead::where('created_at', '>=', $now->copy()->startOfMonth())->count();

        $proposalsByStatus = Proposal::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentLeads = Lead::orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'company_name', 'contact_name', 'status', 'source', 'created_at'])
            ->map(fn ($l) => [
                'company' => $l->company_name,
                'contact' => $l->contact_name,
                'status'  => $l->status,
                'source'  => $l->source,
                'created' => $l->created_at->format('Y-m-d'),
            ])->toArray();

        return [
            'as_of'            => $now->toDateTimeString(),
            'leads_by_status'  => $leadsByStatus,
            'new_leads_month'  => $newThisMonth,
            'proposals'        => $proposalsByStatus,
            'recent_leads'     => $recentLeads,
        ];
    }

    private function fetchInventoryData(): array
    {
        $now = now();

        $lowStock = StockLevel::join('materials', 'stock_levels.material_id', '=', 'materials.id')
            ->where('stock_levels.current_quantity', '<=', DB::raw('materials.minimum_stock_level'))
            ->where('materials.is_active', true)
            ->select('stock_levels.current_quantity', 'materials.name as material_name', 'materials.unit as material_unit', 'materials.minimum_stock_level')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
                'item'          => $s->material_name,
                'quantity'      => (float) $s->current_quantity,
                'minimum_level' => (float) $s->minimum_stock_level,
                'unit'          => $s->material_unit,
            ])->toArray();

        $recentPOs = PurchaseOrder::orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'reference_number', 'status', 'total_amount', 'created_at'])
            ->map(fn ($po) => [
                'ref'    => $po->reference_number,
                'status' => $po->status,
                'total'  => (float) $po->total_amount,
                'date'   => $po->created_at->format('Y-m-d'),
            ])->toArray();

        $poByStatus = PurchaseOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'as_of'          => $now->toDateTimeString(),
            'low_stock_items' => $lowStock,
            'low_stock_count' => count($lowStock),
            'purchase_orders' => $poByStatus,
            'recent_pos'      => $recentPOs,
        ];
    }

    private function fetchAdminTaskData(): array
    {
        $now = now();

        $byStatus = AdminTask::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byCategory = AdminTask::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $overdue = AdminTask::whereNotNull('due_date')
            ->where('due_date', '<', $now)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $urgent = AdminTask::whereNotIn('status', ['completed', 'cancelled'])
            ->where('priority', 'urgent')
            ->count();

        $completedThisWeek = AdminTask::where('status', 'completed')
            ->where('completed_at', '>=', $now->copy()->startOfWeek())
            ->count();

        $open = AdminTask::whereNotIn('status', ['completed', 'cancelled'])
            ->with('assignedTo:id,name')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->limit(20)
            ->get()
            ->map(fn ($t) => [
                'title'       => $t->title,
                'category'    => $t->category,
                'status'      => $t->status,
                'priority'    => $t->priority,
                'assigned_to' => $t->assignedTo?->name ?? 'Unassigned',
                'due_date'    => $t->due_date?->format('Y-m-d'),
                'overdue'     => $t->isOverdue(),
            ])->toArray();

        $unassigned = AdminTask::whereNull('assigned_to')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return [
            'as_of'               => $now->toDateTimeString(),
            'totals_by_status'    => $byStatus,
            'totals_by_category'  => $byCategory,
            'overdue_count'       => $overdue,
            'urgent_count'        => $urgent,
            'unassigned_count'    => $unassigned,
            'completed_this_week' => $completedThisWeek,
            'open_tasks'          => $open,
        ];
    }

    private function generateTopicSummary(string $topic, array $data): string
    {
        $label    = self::TOPIC_LABELS[$topic] ?? $topic;
        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are a smart business analyst for Household Media. Analyze the following live {$label} data and write a concise, insightful summary for the admin.

Keep it tight: 4–6 bullet points max. Highlight what's going well, what needs immediate attention, and the most important numbers. No title needed — start directly with the bullets.

## Live Data

```json
{$dataJson}
```
PROMPT;

        return $this->callGemini($prompt);
    }

    private function callGeminiMultiTurn(array $contents): string
    {
        if (empty($this->apiKey)) {
            return '**Error:** Gemini API key is not configured. Please set `GEMINI_API_KEY` in your `.env` file.';
        }

        $response = Http::timeout(60)->post("{$this->apiUrl}?key={$this->apiKey}", [
            'contents'         => $contents,
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2048],
        ]);

        if ($response->failed()) {
            return '**API Error:** ' . $response->json('error.message', 'Unknown error');
        }

        return $response->json('candidates.0.content.parts.0.text', '*(No content returned)*');
    }

    public function generateReport(Collection $workOrders, string $customInstructions = ''): string
    {
        $workOrderData = $workOrders->map(function ($wo) {
            return [
                'reference' => $wo->reference_number,
                'title' => $wo->title,
                'client' => $wo->client?->company_name ?? 'N/A',
                'department' => $wo->assignedDepartment?->name ?? 'N/A',
                'category' => $wo->category,
                'status' => $wo->status,
                'priority' => $wo->priority,
                'budget' => $wo->budget,
                'actual_cost' => $wo->actual_cost,
                'start_date' => $wo->start_date?->format('Y-m-d'),
                'deadline' => $wo->deadline?->format('Y-m-d'),
                'tasks_count' => $wo->tasks_count ?? 0,
                'completed_tasks' => $wo->completed_tasks_count ?? 0,
            ];
        })->toArray();

        $dataJson = json_encode($workOrderData, JSON_PRETTY_PRINT);

        $systemPrompt = <<<PROMPT
You are a professional business report writer for Household Media, a company that manages work orders for media, civil works, energy, and warehouse projects.

Generate a comprehensive, professional Markdown report based on the following work order data. The report should include:

1. **Executive Summary** – brief overview of the period/jobs covered
2. **Job Overview** – table or structured list of all work orders with key metrics
3. **Performance Highlights** – notable achievements, on-time completions, budget adherence
4. **Concerns & Risks** – overdue jobs, budget overruns, blocked/cancelled work
5. **By Category Breakdown** – summary by category (media, civil_works, energy, warehouse)
6. **Recommendations** – actionable suggestions based on the data

Use Markdown formatting (headings, bold, tables, bullet points). Be concise but thorough.
PROMPT;

        $prompt = $systemPrompt . "\n\n## Work Order Data\n\n```json\n{$dataJson}\n```";

        if ($customInstructions) {
            $prompt .= "\n\n## Additional Instructions\n\n{$customInstructions}";
        }

        return $this->callGemini($prompt);
    }

    public function generateInvoiceNotes(\App\Models\Invoice $invoice): string
    {
        $client  = $invoice->client?->company_name ?? 'the client';
        $ref     = $invoice->workOrder?->reference_number ?? null;
        $total   = number_format((float) $invoice->total, 2);
        $due     = $invoice->due_at?->format('d M Y') ?? 'TBD';
        $refLine = $ref ? "Job Reference: {$ref}" : '';
        $items   = $invoice->items->map(fn ($i) => "- {$i->description} (qty: {$i->quantity}, unit price: \${$i->unit_price})")->implode("\n");

        $prompt = <<<PROMPT
Write a short, professional invoice notes section (2-4 sentences) for the following invoice. Be concise and formal.

Client: {$client}
Total: \${$total}
Due Date: {$due}
{$refLine}
Line Items:
{$items}

Return only the notes text, no preamble.
PROMPT;

        return $this->callGemini($prompt);
    }

    public function suggestInvoiceItems(\App\Models\Invoice $invoice): array
    {
        $workOrder = $invoice->workOrder;
        if (!$workOrder) {
            return [];
        }

        $workOrder->load('tasks');

        $tasks = $workOrder->tasks->map(fn ($t) => [
            'title'            => $t->title,
            'actual_hours'     => (float) $t->actual_hours,
            'estimated_hours'  => (float) $t->estimated_hours,
            'status'           => $t->status,
        ])->toArray();

        $woRef      = $workOrder->reference_number;
        $woTitle    = $workOrder->title;
        $woCategory = $workOrder->category;
        $tasksJson    = json_encode($tasks, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are a billing assistant. Based on the following work order data, suggest invoice line items.

Work Order: {$woRef} — {$woTitle}
Category: {$woCategory}

Tasks:
{$tasksJson}

Return ONLY a valid JSON array of line items. Each item must have:
- "description" (string)
- "quantity" (number)
- "unit" (string, e.g. "hrs", "days", "each")
- "unit_price" (number)
- "total" (number, = quantity * unit_price)

Example: [{"description":"Site survey","quantity":2,"unit":"hrs","unit_price":75,"total":150}]

Return only the JSON array, nothing else.
PROMPT;

        $raw = $this->callGemini($prompt);

        // Strip markdown code blocks if present
        $raw = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $raw = preg_replace('/```\s*$/m', '', $raw);
        $raw = trim($raw);

        $items = json_decode($raw, true);
        return is_array($items) ? $items : [];
    }

    public function generateInvoiceReport(\Illuminate\Support\Collection $invoices, string $instructions = ''): string
    {
        $data = $invoices->map(fn ($inv) => [
            'invoice_number' => $inv->invoice_number,
            'client' => $inv->client?->company_name ?? 'N/A',
            'work_order' => $inv->workOrder?->reference_number ?? 'N/A',
            'status' => $inv->status,
            'subtotal' => (float) $inv->subtotal,
            'tax_amount' => (float) $inv->tax_amount,
            'total' => (float) $inv->total,
            'currency' => $inv->currency,
            'issued_at' => $inv->issued_at?->format('Y-m-d'),
            'due_at' => $inv->due_at?->format('Y-m-d'),
            'paid_at' => $inv->paid_at?->format('Y-m-d'),
        ])->toArray();

        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        $systemPrompt = <<<PROMPT
You are a financial analyst for Household Media. Generate a professional Markdown invoice/revenue report from the data below.

DO NOT include a report title, generation date, or any disclaimers like "Calculations for overdue are based on this date". Just provide the report body directly starting with the Summary.

Include:
1. **Summary** – total invoiced, collected, outstanding, overdue
2. **Invoice Breakdown** – table with key columns
3. **By Status** – count and totals per status
4. **Client Revenue** – top clients by total invoiced
5. **Observations & Recommendations** – payment trends, overdue risks

Use clear Markdown with tables and bullet points.
PROMPT;

        $prompt = $systemPrompt . "\n\n## Invoice Data\n\n```json\n{$dataJson}\n```";
        if ($instructions) {
            $prompt .= "\n\n## Additional Instructions\n\n{$instructions}";
        }

        return $this->callGemini($prompt);
    }

    public function reviseReport(string $currentMarkdown, string $instructions): string
    {
        $prompt = <<<PROMPT
You are a professional business report editor. Below is an existing Markdown report. Please revise it according to the instructions provided.

Return only the revised Markdown report — no preamble, no explanation, just the updated report content.

## Current Report

{$currentMarkdown}

## Revision Instructions

{$instructions}
PROMPT;

        return $this->callGemini($prompt);
    }

    private function callGemini(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return "**Error:** Gemini API key is not configured. Please set `GEMINI_API_KEY` in your `.env` file.";
        }

        $response = Http::timeout(60)->post("{$this->apiUrl}?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 8192,
            ],
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Unknown error');
            return "**API Error:** {$error}";
        }

        return $response->json('candidates.0.content.parts.0.text', '*(No content returned from AI)*');
    }
}
