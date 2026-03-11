<?php

namespace App\Console\Commands;

use App\Models\NotificationRule;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Material;
use App\Models\Invoice;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Process notification rules and send scheduled reminders';

    public function handle(): int
    {
        $rules = NotificationRule::active()->get();
        $sent = 0;

        foreach ($rules as $rule) {
            $sent += match ($rule->rule_type) {
                'deadline_reminder' => $this->processDeadlineReminders($rule),
                'task_overdue'      => $this->processOverdueTasks($rule),
                'budget_threshold'  => $this->processBudgetAlerts($rule),
                'stock_low'         => $this->processLowStock($rule),
                'invoice_overdue'   => $this->processOverdueInvoices($rule),
                default             => 0,
            };
        }

        $this->info("✅ Sent {$sent} notification(s) from " . $rules->count() . " active rules.");
        return self::SUCCESS;
    }

    private function processDeadlineReminders(NotificationRule $rule): int
    {
        $days = $rule->trigger_days ?? (int) $rule->value;
        $targetDate = now()->addDays($days)->toDateString();
        $count = 0;

        // Work order deadlines
        $workOrders = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('deadline', $targetDate)
            ->get();

        foreach ($workOrders as $wo) {
            $recipients = $this->getRecipients($rule, $wo->created_by);
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Deadline Approaching')
                    ->body("{$wo->reference_number}: {$wo->title} — due in {$days} day(s)")
                    ->icon('heroicon-o-clock')
                    ->warning()
                    ->sendToDatabase($user);
                $count++;
            }
        }

        // Task deadlines
        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('deadline', $targetDate)
            ->get();

        foreach ($tasks as $task) {
            if ($task->assigned_to) {
                $user = User::find($task->assigned_to);
                if ($user) {
                    Notification::make()
                        ->title('Task Deadline Approaching')
                        ->body("{$task->title} — due in {$days} day(s)")
                        ->icon('heroicon-o-clock')
                        ->warning()
                        ->sendToDatabase($user);
                    $count++;
                }
            }
        }

        return $count;
    }

    private function processOverdueTasks(NotificationRule $rule): int
    {
        $count = 0;
        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->where('deadline', '<', now())
            ->get();

        foreach ($tasks as $task) {
            $daysOverdue = now()->diffInDays($task->deadline);
            $recipients = $this->getRecipients($rule, $task->assigned_to);

            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Task Overdue')
                    ->body("{$task->title} is {$daysOverdue} day(s) overdue")
                    ->icon('heroicon-o-exclamation-triangle')
                    ->danger()
                    ->sendToDatabase($user);
                $count++;
            }
        }

        return $count;
    }

    private function processBudgetAlerts(NotificationRule $rule): int
    {
        $threshold = $rule->getNumericValue();
        $count = 0;

        $workOrders = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('budget')
            ->where('budget', '>', 0)
            ->whereNotNull('actual_cost')
            ->get()
            ->filter(fn ($wo) => ($wo->actual_cost / $wo->budget) * 100 >= $threshold);

        foreach ($workOrders as $wo) {
            $pct = round(($wo->actual_cost / $wo->budget) * 100);
            $recipients = $this->getRecipients($rule);

            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Budget Alert')
                    ->body("{$wo->reference_number} at {$pct}% of budget (\${$wo->actual_cost}/\${$wo->budget})")
                    ->icon('heroicon-o-banknotes')
                    ->danger()
                    ->sendToDatabase($user);
                $count++;
            }
        }

        return $count;
    }

    private function processLowStock(NotificationRule $rule): int
    {
        $count = 0;
        $materials = Material::where('is_active', true)
            ->whereHas('stockLevel', function ($q) {
                $q->whereColumn('current_quantity', '<=', 'materials.minimum_stock_level');
            })
            ->with('stockLevel')
            ->get();

        foreach ($materials as $material) {
            $recipients = $this->getRecipients($rule);
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Low Stock Alert')
                    ->body("{$material->name}: {$material->stockLevel->current_quantity} remaining (min: {$material->minimum_stock_level})")
                    ->icon('heroicon-o-archive-box')
                    ->warning()
                    ->sendToDatabase($user);
                $count++;
            }
        }

        return $count;
    }

    private function processOverdueInvoices(NotificationRule $rule): int
    {
        $count = 0;
        $invoices = Invoice::where('status', 'sent')
            ->where('due_at', '<', now())
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $daysOverdue = now()->diffInDays($invoice->due_at);
            $recipients = $this->getRecipients($rule);

            foreach ($recipients as $user) {
                Notification::make()
                    ->title('Invoice Overdue')
                    ->body("Invoice {$invoice->invoice_number} for {$invoice->client->company_name} — {$daysOverdue} day(s) overdue (\${$invoice->total})")
                    ->icon('heroicon-o-document-currency-dollar')
                    ->danger()
                    ->sendToDatabase($user);
                $count++;
            }
        }

        return $count;
    }

    private function getRecipients(NotificationRule $rule, ?int $specificUserId = null): array
    {
        $users = collect();

        if ($rule->applies_to_role) {
            $users = User::role($rule->applies_to_role)->get();
        } else {
            $users = User::role(['super_admin', 'manager'])->get();
        }

        if ($specificUserId) {
            $specificUser = User::find($specificUserId);
            if ($specificUser) {
                $users = $users->push($specificUser)->unique('id');
            }
        }

        return $users->all();
    }
}
