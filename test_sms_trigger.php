<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\NotificationLog;
use App\Models\Task;
use App\Models\User;

$staff    = User::where('email', 'staff@householdmedia.co.zw')->first();
$manager  = User::role('manager')->first();

echo "=== SMS Integration Test ===" . PHP_EOL;
echo "Staff: {$staff->name} ({$staff->phone_number})" . PHP_EOL;
echo "Manager: {$manager->name} ({$manager->phone_number})" . PHP_EOL . PHP_EOL;

$logsBefore = NotificationLog::where('channel', 'sms')->count();

// ── Test 1: Task assignment ──────────────────────────────────────────────────
$task = Task::whereNull('assigned_to')
    ->whereNotIn('status', ['completed', 'cancelled'])
    ->first();

if ($task) {
    echo "[1] Assigning task \"{$task->title}\" to {$staff->name}..." . PHP_EOL;
    $task->update(['assigned_to' => $staff->id]);
    echo "    Observer fired. Task saved." . PHP_EOL;
} else {
    echo "[1] No unassigned task available — skipping." . PHP_EOL;
}

// ── Test 2: Expense approval ─────────────────────────────────────────────────
$expense = \App\Models\Expense::where('approval_status', 'pending')
    ->whereNotNull('submitted_by')
    ->first();

if ($expense) {
    $submitter = User::find($expense->submitted_by);
    echo "[2] Approving expense #{$expense->id} (submitted by {$submitter?->name})..." . PHP_EOL;
    $expense->update(['approval_status' => 'approved', 'approved_by' => $manager->id]);
    echo "    Observer fired. Expense approved." . PHP_EOL;
} else {
    echo "[2] No pending expense — skipping." . PHP_EOL;
}

// ── Test 3: Leave request approval ──────────────────────────────────────────
$leave = \App\Models\StaffAvailability::where('status', 'pending')->first();

if ($leave) {
    $requester = User::find($leave->user_id);
    echo "[3] Approving leave for {$requester?->name}..." . PHP_EOL;
    $leave->update(['status' => 'approved', 'approved_by' => $manager->id]);
    echo "    Observer fired. Leave approved." . PHP_EOL;
} else {
    echo "[3] No pending leave request — skipping." . PHP_EOL;
}

echo PHP_EOL . "Waiting 4s for queue worker to process..." . PHP_EOL;
sleep(4);

// ── Results ──────────────────────────────────────────────────────────────────
$logsAfter = NotificationLog::where('channel', 'sms')->count();
$newLogs   = $logsAfter - $logsBefore;

echo PHP_EOL . "=== Results ===" . PHP_EOL;
echo "New SMS log entries: {$newLogs}" . PHP_EOL . PHP_EOL;

NotificationLog::where('channel', 'sms')
    ->latest()
    ->limit($newLogs ?: 3)
    ->get()
    ->each(function ($l) {
        $icon = $l->status === 'sent' ? '✅' : '❌';
        echo "{$icon} [{$l->status}] {$l->event_type}" . PHP_EOL;
        echo "   Provider ID: {$l->provider_message_id}" . PHP_EOL;
        echo "   Recipient user ID: {$l->notifiable_id}" . PHP_EOL;
    });
