<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Services\StatisticsService;
use Illuminate\Console\Command;

class RebuildStatisticsCommand extends Command
{
    protected $signature = 'ktms:rebuild-stats
                            {--tournament= : Limit the rebuild to a single tournament id}';

    protected $description = 'Rebuild player statistic aggregates from the stored raid and defensive action records';

    public function handle(StatisticsService $statistics): int
    {
        $tournaments = Tournament::query()
            ->when($this->option('tournament'), fn ($query, $id) => $query->whereKey($id))
            ->get();

        if ($tournaments->isEmpty()) {
            $this->warn('No tournaments found.');

            return self::SUCCESS;
        }

        foreach ($tournaments as $tournament) {
            $count = $statistics->recalculateTournament($tournament);
            $this->line("Rebuilt {$count} player records for \"{$tournament->name}\".");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
