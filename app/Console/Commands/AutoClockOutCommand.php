<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoClockOutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out employees who have been clocked in for 9 hours or more (compensating for 1-hour break)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for employees who need auto clock-out...');

        // Find attendances where:
        // - time_in is set
        // - time_out is NOT set
        // - time_in was 9 or more hours ago (compensating for 1-hour break)
        $attendances = Attendance::whereNotNull('time_in')
            ->whereNull('time_out')
            ->where('date', '>=', now()->subDays(2)->toDateString()) // Only check recent attendances
            ->get();

        $autoClockedOutCount = 0;

        foreach ($attendances as $attendance) {
            try {
                // Parse time_in - handle both H:i format and Carbon objects
                if ($attendance->time_in instanceof \Carbon\Carbon) {
                    $timeIn = $attendance->time_in;
                } else {
                    $timeIn = Carbon::parse($attendance->time_in);
                }
                
                // Create a datetime for the attendance date with the time_in time
                $clockInDateTime = Carbon::parse($attendance->date)->setTime($timeIn->hour, $timeIn->minute);
                
                // Calculate hours elapsed since clock in
                $hoursElapsed = $clockInDateTime->diffInHours(now());
                
                // If 9 or more hours have passed, auto clock out (compensating for 1-hour break)
                if ($hoursElapsed >= 9) {
                    // Set time_out to 9 hours after time_in (compensating for 1-hour break)
                    $clockOutDateTime = $clockInDateTime->copy()->addHours(9);
                    $attendance->time_out = $clockOutDateTime->format('H:i');
                    $attendance->save();
                    
                    $this->info("Auto clocked out employee ID {$attendance->employee_id} for date {$attendance->date} (clocked in at {$attendance->time_in}, auto clocked out at {$attendance->time_out})");
                    
                    // Log the action
                    \Log::info('Auto clock out', [
                        'attendance_id' => $attendance->id,
                        'employee_id' => $attendance->employee_id,
                        'date' => $attendance->date,
                        'time_in' => $attendance->time_in,
                        'auto_time_out' => $attendance->time_out,
                        'hours_elapsed' => $hoursElapsed,
                    ]);
                    
                    $autoClockedOutCount++;
                }
            } catch (\Exception $e) {
                $this->error("Error processing attendance ID {$attendance->id}: " . $e->getMessage());
                \Log::error('Auto clock out error', [
                    'attendance_id' => $attendance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Auto clock-out completed. {$autoClockedOutCount} employee(s) automatically clocked out.");
        
        return Command::SUCCESS;
    }
}
