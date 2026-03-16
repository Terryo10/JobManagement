<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Material;
use App\Models\NotificationRule;
use App\Models\Task;
use App\Models\WorkOrder;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Process notification rules and send scheduled reminders';

    public function handle(NotificationRouter $router): int
    {
        $rules = NotificationRule::active()->get();
        $sent  = 0;

        foreach ($rules as $rule) {
            $sent += match ($rule->rule_type) {
                'deadline_reminder' => $this->processDeadlineReminders($rule, $router),
                'task_overdue'      => $this->processOverdueTasks($rule, $router),
                'budget_threshold'  => $this->processBudgetAlerts($rule, $router),
                'stock_low'         => $this->processLowStock($rule, $router),
                'invoice_overdue'   => $this->processOverdueInvoices($rule, $router),
                default             => 0,
            };
        }

        $this->info("✅ Sent {$sent} notification(s) from " . $rules->count() . " active rules.");

        return self::SUCCESS;
    }

    private function processDeadlineReminders(NotificationRule $rule, NotificationRouter $router): int
    {
        $days       = $rule->trigger_days ?? (int) $rule->value;
        $targetDate = now()->addDays($days)->toDateString();
        $count      = 0;

        $workOrders = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('deadline', $targetDate)
            ->get();

        foreach ($workOrders as $wo) {
            $recipientIds = $this->getRecipientIds($rule, $wo->created_by);
            if (empty($recipientIds)) continue;

            $router->dispatch(new NotificationEvent(
                type:             'work_order.deadline_approaching',
                title:            'Deadline Approaching',
                body:             "{$wo->reference_number}: {$wo->title} — due in {$days} day(s)",
                icon:             'heroicon-o-clock',
                color:            'warning',
                recipientUserIds: $recipientIds,
                subjectType:      WorkOrder::class,
                subjectId:        $wo->id,
                priority:         'high',
                idempotencyKey:   "deadline_reminder_{$wo->id}_" . now()->toDateString(),
            ));
            $count += count($recipientIds);
        }

        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('deadline', $targetDate)
            ->get();

        foreach ($tasks as $task) {
            $recipientIds = $this->getRecipientIds($rule, $task->assigned_to);
            if (empty($recipientIds)) continue;

            $router->dispatch(new NotificationEvent(
                type:             'task.deadline_approaching',
                title:            'Task Deadline Approaching',
                body:             "{$task->title} — due in {$days} day(s)",
                icon:             'heroicon-o-clock',
                color:            'warning',
                recipientUserIds: $recipientIds,
                subjectType:      Task::class,
                subjectId:        $task->id,
                priority:         'high',
                idempotencyKey:   "task_deadline_{$task->id}_" . now()->toDateString(),
            ));
            $count += count($recipientIds);
        }

        return $count;
    }

    private function processOverdueTasks(NotificationRule $rule, NotificationRouter $router): int
    {
        $count = 0;
        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->where('deadline', '<', now())
            ->get();

        foreach ($tasks as $task) {
            $daysOverdue  = (int) now()->diffInDays($task->deadline);
            $recipientIds = $this->getRecipientIds($rule, $task->assigned_to);
            if (empty($recipientIds)) continue;

            $router->dispatch(new NotificationEvent(
                type:             'task.overdue',
                title:            'Task Overdue',
                body:             "{$task->title} is {$daysOverdue} day(s) overdue",
                icon:             'heroicon-o-exclamation-triangle',
                color:            'danger',
                recipientUserIds: $recipientIds,
                subjectType:      Task::class,
                subjectId:        $task->id,
                priority:         'high',
                idempotencyKey:   "task_overdue_{$task->id}_" . now()->toDateString(),
            ));
            $count += count($recipientIds);
        }

        return $count;
    }

    private function processBudgetAlerts(NotificationRule $rule, NotificationRouter $router): int
    {
        $threshold  = $rule->getNumericValue();
        $count      = 0;

        $workOrders = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('budget')
            ->where('budget', '>', 0)
            ->whereNotNull('actual_cost')
            ->get()
            ->filter(fn ($wo) => ($wo->actual_cost / $wo->budget) * 100 >= $threshold);

        foreach ($workOrders as $wo) {
            $pct          = round(($wo->actual_cost / $wo->budget) * 100);
            $recipientIds = $this->getRecipientIds($rule);
            if (empty($recipientIds)) continue;

            $router->dispatch(new NotificationEvent(
                type:             'work_order.budget_alert',
                title:            'Budget Alert',
                body:             "{$wo->reference_number} at {$pct}% of budget (\${$wo->actual_cost}/\${$wo->budget})",
                icon:             'heroicon-o-banknotes',
                color:            'danger',
                recipientUserIds: $recipientIds,
                subjectType:      WorkOrder::class,
                subjectId:        $wo->id,
                priority:         'high',
                idempotencyKey:   "budget_alert_{$wo->id}_" . now()->toDateString(),
            ));
            $count += count($recipientIds);
        }

        return $count;
    }

    private function processLowStock(NotificationRule $rule, NotificationRouter $router): int
    {
        $count = 0;
        $materials = Material::where('is_active', true)
            ->whereNotNull('minimum_stock_level')
            ->with('stockLevel')
            ->get()
            ->filter(fn ($m) => $m->stockLevel && $m->stockLevel->current_quantity <= $m->minimum_stock_level);

        foreach ($materials as $material) {
            $recipientIds = $this->getRecipientIds($rule);
            if (empty($recipientIds)) continue;

            $router->dispatch(new NotificationEvent(
                type:             'stock.low',
                title:            'Low Stock Alert',
                body:             "{$material->name}: {$material->stockLevel->current_quantity} remaining (min: {$material->minimum_stock_level})",
                icon:             'heroicon-o-archive-box',
                color:            'warning',
                recipientUserIds: $recipientIds,
                subjectType:      Material::class,
                subjectId:        $material->id,
                priority:         'high',
                idempotencyKey:   "low_stock_{$material->id}_" . now()->toDateString(),
            ));
            $count += count($recipientIds);
        }

        return $count;
    }

    private function processOverdueInvoices(NotificationRule $rule, NotificationRouter $router): int
    {
        $count    = 0;
        $invoices = Invoice::where('status', 'sent')
            ->where('due_at', '<', now())
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => 'overdue']);

            $daysOverdue  = (int) now()->diffInDays($invoice->due_at);
            $clientName   = $invoice->client?->company_name ?? 'Unknown Client';
            $recipientIds = $this->getRecipientIds($rule);
            if (empty($recipientIds)) continue;

            $router->dispatch(new NotificationEvent(
                type:             'invoice.overdue',
                title:            'Invoice Overdue',
                body:             "Invoice {$invoice->invoice_number} for {$clientName} — {$daysOverdue} day(s) overdue (\${$invoice->total})",
                icon:             'heroicon-o-document-currency-dollar',
                color:            'danger',
                recipientUserIds: $recipientIds,
                subjectType:      Invoice::class,
                subjectId:        $invoice->id,
                priority:         'critical',
                idempotencyKey:   "invoice_overdue_{$invoice->id}_" . now()->toDateString(),
            ));
            $count += count($recipientIds);
        }

        return $count;
    }

    /** @return int[] */
    private function getRecipientIds(NotificationRule $rule, ?int $specificUserId = null): array
    {
        if ($rule->applies_to_role) {
            $ids = \App\Models\User::role($rule->applies_to_role)->pluck('id')->toArray();
        } else {
            $ids = \App\Models\User::role(['super_admin', 'manager'])->pluck('id')->toArray();
        }

        if ($specificUserId && ! in_array($specificUserId, $ids)) {
            $ids[] = $specificUserId;
        }

        return array_unique($ids);
    }
}
