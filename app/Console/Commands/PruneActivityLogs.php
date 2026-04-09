<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'logs:prune {--days=90 : Number of days to keep}';
    protected $description = 'Delete activity logs older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = ActivityLog::olderThan($days)->delete();

        $this->info("Pruned {$deleted} activity log(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
