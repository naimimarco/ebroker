<?php

namespace App\Console\Commands\Demo;

use Exception;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:remove-properties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Properties From Demo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Delete Before 15 Days
            $propertyData = Property::where('request_status', 'pending')
                ->where('created_at', '<', now()->subDays(15))
                ->get();


            if ($propertyData->count() > 0) {
                foreach ($propertyData as $property) {
                    $property->delete();
                }
                Log::info('Successfully deleted ' . $propertyData->count() . ' properties');
            } else {
                Log::info('No properties found to delete');
            }
        } catch (Exception $e) {
            Log::error('Issue Removing Properties From Demo: ' . $e->getMessage());
        }
    }
}
