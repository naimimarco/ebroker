<?php

namespace App\Console\Commands\Demo;

use Exception;
use App\Models\Projects;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:remove-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Projects From Demo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Delete Before 15 Days
            $ProjectData = Projects::where('request_status', 'pending')->where('created_at', '<', now()->subDays(15))->get();
            if ($ProjectData->count() > 0) {
                foreach ($ProjectData as $project) {
                    $project->delete();
                }
            }
            Log::info('All projects have been deleted');
        } catch (Exception $e) {
            dd($e);
            Log::error('Issue Removing Projects From Demo: ' . $e->getMessage());
        }
    }
}
