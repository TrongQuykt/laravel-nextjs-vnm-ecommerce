<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyReportJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule daily report to be sent to admins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            SendDailyReportJob::dispatch();
            
            $this->info('Daily report job dispatched successfully.');
            Log::info('Daily report job dispatched successfully.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to dispatch daily report job: ' . $e->getMessage());
            Log::error('Failed to dispatch daily report job', ['error' => $e->getMessage()]);
            
            return Command::FAILURE;
        }
    }
}
