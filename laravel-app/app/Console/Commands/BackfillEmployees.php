<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill the employees table using distinct names from the attendance table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching distinct employees from attendance table...');

        $distinctEmployees = DB::table('attendance')
            ->select('employee_name', 'category')
            ->whereNotNull('employee_name')
            ->distinct()
            ->get();

        $this->info("Found {$distinctEmployees->count()} distinct employees.");

        $inserted = 0;
        $skipped = 0;

        foreach ($distinctEmployees as $emp) {
            $exists = DB::table('employees')
                ->where('employee_name', $emp->employee_name)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('employees')->insert([
                'employee_name' => $emp->employee_name,
                'category' => $emp->category,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        $this->info("Backfill complete. Inserted: {$inserted}, Skipped (already existed): {$skipped}.");

        return Command::SUCCESS;
    }
}