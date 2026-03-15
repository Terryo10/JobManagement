<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\User;
use App\Notifications\DatabaseAlert;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        $submittedBy = $expense->submittedBy?->name ?? 'Someone';
        $workOrderRef = $expense->workOrder?->reference_number ?? 'N/A';
        $amount = number_format((float) $expense->amount, 2);

        $managers = User::role(['manager', 'super_admin'])->get();
        foreach ($managers as $manager) {
            $manager->notify(new DatabaseAlert(
                title: 'New Expense Submitted',
                body: "{$submittedBy} submitted a {$expense->category} expense of {$expense->currency} {$amount} for {$workOrderRef}",
                icon: 'heroicon-o-banknotes',
                color: 'warning',
            ));
        }
    }
}
