<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AiReportService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
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
