<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeDemoData extends Command
{
    protected $signature = 'app:purge-demo-data
                            {--force : Skip the confirmation prompt}
                            {--dry-run : Preview what would be deleted without making any changes}';

    protected $description = 'Remove all seeded demo data while preserving roles, departments, notification rules, and rate cards';

    /**
     * Emails seeded by DatabaseSeeder — these are the demo accounts.
     * Only users whose email appears in this list will be deleted.
     */
    protected array $demoEmails = [
        'admin@householdmedia.co.zw',
        'manager@householdmedia.co.zw',
        'accountant@householdmedia.co.zw',
        'depthead@householdmedia.co.zw',
        'staff@householdmedia.co.zw',
        'staff2@householdmedia.co.zw',
        'staff3@householdmedia.co.zw',
        'client@example.com',
        'marketing@householdmedia.co.zw',
    ];

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->newLine();
        $this->line(
            $isDryRun
                ? '  <fg=cyan;options=bold>DRY RUN</> — no changes will be made'
                : '  <fg=red;options=bold>LIVE PURGE</> — data will be <fg=red>permanently deleted</>'
        );
        $this->newLine();

        $preview = $this->buildPreview();
        $this->renderPreview($preview);

        $total = array_sum(array_column($preview, 'count'));

        if ($total === 0) {
            $this->info('No demo data found. Nothing to purge.');
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->newLine();
            $this->line('  Run without <fg=cyan>--dry-run</> to apply these deletions.');
            return self::SUCCESS;
        }

        if (
            ! $this->option('force') &&
            ! $this->confirm('Are you sure you want to permanently delete all of the above demo data?', false)
        ) {
            $this->line('Purge cancelled — no changes were made.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->purge();
        $this->newLine();
        $this->info('✅  Demo data purged successfully.');
        $this->line('   Preserved: <fg=green>roles</>, <fg=green>departments</>, <fg=green>notification rules</>, <fg=green>rate cards</>');
        $this->newLine();

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Preview
    // ──────────────────────────────────────────────────────────────────────────

    protected function buildPreview(): array
    {
        $demoUserIds  = User::whereIn('email', $this->demoEmails)->pluck('id');
        $workOrderIds = DB::table('work_orders')->pluck('id');
        $taskIds      = DB::table('tasks')->whereIn('work_order_id', $workOrderIds)->pluck('id');
        $invoiceIds   = DB::table('invoices')->pluck('id');
        $poIds        = DB::table('purchase_orders')->pluck('id');
        $leadIds      = DB::table('leads')->pluck('id');
        $materialIds  = DB::table('materials')->pluck('id');
        $fileIds      = DB::table('personal_files')->whereIn('user_id', $demoUserIds)->pluck('id');

        return [
            ['label' => 'Invoice items',                'count' => DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->count()],
            ['label' => 'Invoices',                     'count' => DB::table('invoices')->count()],
            ['label' => 'Purchase order items',         'count' => DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->count()],
            ['label' => 'Purchase orders',              'count' => DB::table('purchase_orders')->count()],
            ['label' => 'Stock levels',                 'count' => DB::table('stock_levels')->whereIn('material_id', $materialIds)->count()],
            ['label' => 'Task comments',                'count' => DB::table('task_comments')->whereIn('task_id', $taskIds)->count()],
            ['label' => 'Task time logs',               'count' => DB::table('task_time_logs')->whereIn('task_id', $taskIds)->count()],
            ['label' => 'Tasks',                        'count' => DB::table('tasks')->whereIn('work_order_id', $workOrderIds)->count()],
            ['label' => 'Work order materials',         'count' => DB::table('work_order_materials')->whereIn('work_order_id', $workOrderIds)->count()],
            ['label' => 'Work order collaborators',     'count' => DB::table('work_order_collaborators')->whereIn('work_order_id', $workOrderIds)->count()],
            ['label' => 'Work order notes',             'count' => DB::table('work_order_notes')->whereIn('work_order_id', $workOrderIds)->count()],
            ['label' => 'Work orders',                  'count' => DB::table('work_orders')->count()],
            ['label' => 'Lead communications',          'count' => DB::table('lead_communications')->whereIn('lead_id', $leadIds)->count()],
            ['label' => 'Leads',                        'count' => DB::table('leads')->count()],
            ['label' => 'Proposals',                    'count' => DB::table('proposals')->count()],
            ['label' => 'Market research',              'count' => DB::table('market_research')->count()],
            ['label' => 'Networking events',            'count' => DB::table('networking_events')->count()],
            ['label' => 'Business reports',             'count' => DB::table('business_reports')->count()],
            ['label' => 'Clients',                      'count' => DB::table('clients')->count()],
            ['label' => 'Materials',                    'count' => DB::table('materials')->count()],
            ['label' => 'Suppliers',                    'count' => DB::table('suppliers')->count()],
            ['label' => 'Personal file shares',         'count' => DB::table('personal_file_shares')->whereIn('personal_file_id', $fileIds)->count()],
            ['label' => 'Personal files (demo users)',  'count' => (int) $fileIds->count()],
            ['label' => 'Notification preferences',     'count' => DB::table('notification_preferences')->whereIn('user_id', $demoUserIds)->count()],
            ['label' => 'User skills (demo users)',     'count' => DB::table('user_skills')->whereIn('user_id', $demoUserIds)->count()],
            ['label' => 'Staff availability (demo)',    'count' => DB::table('staff_availability')->whereIn('user_id', $demoUserIds)->count()],
            ['label' => 'Role assignments (demo users)','count' => DB::table('model_has_roles')->whereIn('model_id', $demoUserIds)->where('model_type', User::class)->count()],
            ['label' => 'Demo users',                   'count' => (int) $demoUserIds->count()],
        ];
    }

    protected function renderPreview(array $preview): void
    {
        $rows = array_map(fn ($row) => [
            $row['label'],
            $row['count'] > 0 ? "<fg=red>{$row['count']}</>" : '<fg=green>0</>',
        ], $preview);

        $this->table(['Data Type', 'Records'], $rows);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Purge (runs inside a single transaction so it fully succeeds or rolls back)
    // ──────────────────────────────────────────────────────────────────────────

    protected function purge(): void
    {
        DB::transaction(function () {
            $demoUserIds  = User::whereIn('email', $this->demoEmails)->pluck('id');
            $workOrderIds = DB::table('work_orders')->pluck('id');
            $taskIds      = DB::table('tasks')->whereIn('work_order_id', $workOrderIds)->pluck('id');
            $invoiceIds   = DB::table('invoices')->pluck('id');
            $poIds        = DB::table('purchase_orders')->pluck('id');
            $leadIds      = DB::table('leads')->pluck('id');
            $materialIds  = DB::table('materials')->pluck('id');
            $fileIds      = DB::table('personal_files')->whereIn('user_id', $demoUserIds)->pluck('id');

            // ── Invoices ──────────────────────────────────────────────────────
            $this->step('Invoice items',        DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete());
            $this->step('Invoices',             DB::table('invoices')->delete());

            // ── Purchase orders ───────────────────────────────────────────────
            $this->step('Purchase order items', DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->delete());
            $this->step('Purchase orders',      DB::table('purchase_orders')->delete());

            // ── Stock & materials ─────────────────────────────────────────────
            $this->step('Stock levels',         DB::table('stock_levels')->whereIn('material_id', $materialIds)->delete());

            // ── Tasks & work orders ───────────────────────────────────────────
            $this->step('Task comments',        DB::table('task_comments')->whereIn('task_id', $taskIds)->delete());
            $this->step('Task time logs',       DB::table('task_time_logs')->whereIn('task_id', $taskIds)->delete());
            $this->step('Tasks',                DB::table('tasks')->whereIn('work_order_id', $workOrderIds)->delete());

            $this->step('Work order materials',     DB::table('work_order_materials')->whereIn('work_order_id', $workOrderIds)->delete());
            $this->step('Work order collaborators', DB::table('work_order_collaborators')->whereIn('work_order_id', $workOrderIds)->delete());
            $this->step('Work order notes',         DB::table('work_order_notes')->whereIn('work_order_id', $workOrderIds)->delete());
            $this->step('Work orders',              DB::table('work_orders')->delete());

            // ── Leads ─────────────────────────────────────────────────────────
            $this->step('Lead communications',  DB::table('lead_communications')->whereIn('lead_id', $leadIds)->delete());
            $this->step('Leads',                DB::table('leads')->delete());

            // ── Marketing ─────────────────────────────────────────────────────
            $this->step('Proposals',            DB::table('proposals')->delete());
            $this->step('Market research',      DB::table('market_research')->delete());
            $this->step('Networking events',    DB::table('networking_events')->delete());
            $this->step('Business reports',     DB::table('business_reports')->delete());

            // ── Clients ───────────────────────────────────────────────────────
            $this->step('Clients',              DB::table('clients')->delete());

            // ── Inventory ─────────────────────────────────────────────────────
            $this->step('Materials',            DB::table('materials')->delete());
            $this->step('Suppliers',            DB::table('suppliers')->delete());

            // ── Demo users & their data ───────────────────────────────────────
            $this->step('Personal file shares',     DB::table('personal_file_shares')->whereIn('personal_file_id', $fileIds)->delete());
            $this->step('Personal files',           DB::table('personal_files')->whereIn('user_id', $demoUserIds)->delete());
            $this->step('Notification preferences', DB::table('notification_preferences')->whereIn('user_id', $demoUserIds)->delete());
            $this->step('User skills',              DB::table('user_skills')->whereIn('user_id', $demoUserIds)->delete());
            $this->step('Staff availability',       DB::table('staff_availability')->whereIn('user_id', $demoUserIds)->delete());
            $this->step('Role assignments',         DB::table('model_has_roles')->whereIn('model_id', $demoUserIds)->where('model_type', User::class)->delete());
            $this->step('Demo users',               DB::table('users')->whereIn('id', $demoUserIds)->delete());
        });
    }

    protected function step(string $label, int $deleted): void
    {
        if ($deleted > 0) {
            $this->line("  <fg=green>✓</> Deleted <fg=yellow>{$deleted}</> {$label}");
        }
    }
}
