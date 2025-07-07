<?php

namespace App\Console\Commands\Demo;

use App\Models\Advertisement;
use Exception;
use App\Models\Projects;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveAdvertisements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:remove-advertisements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Advertisements From Demo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Delete Before 15 Days
            $propertyId = Property::where('request_status', 'approved')->where('created_at', '<', now()->subDays(15))->pluck('id');
            $projectId = Projects::where('request_status', 'approved')->where('created_at', '<', now()->subDays(15))->pluck('id');
            $advertisementData = Advertisement::whereNotIn('property_id', $propertyId)->whereNotIn('project_id', $projectId)->get();
            if ($advertisementData->count() > 0) {
                foreach ($advertisementData as $advertisement) {
                    $advertisement->delete();
                }
            }
            Log::info('All advertisements have been deleted');
        } catch (Exception $e) {
            Log::error('Issue Removing Advertisements From Demo: ' . $e->getMessage());
        }
    }
}
