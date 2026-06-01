<?php

namespace App\Console\Commands;

use App\Services\StockService;
use Illuminate\Console\Command;

class ReleaseExpiredReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired stock reservations';

    /**
     * Execute the console command.
     */
    public function handle(StockService $stockService): int
    {
        $this->info('Checking for expired reservations...');
        
        $count = $stockService->releaseExpiredReservations();
        
        $this->info("Released {$count} expired reservations.");
        
        return Command::SUCCESS;
    }
}
