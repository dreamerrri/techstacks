<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class GenerateHrAdminNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:generate-hr-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate HR/Admin notifications for unassigned employees, missing gov IDs, overdue payrolls, and expiring allowances/benefits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating HR/Admin notifications...');
        
        NotificationService::generateHrAdminNotifications();
        
        $this->info('HR/Admin notifications generated successfully.');
        
        return Command::SUCCESS;
    }
}
