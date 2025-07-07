<?php

namespace App\Console\Commands\Demo;

use App\Models\Advertisement;
use App\Models\Chats;
use Exception;
use App\Models\Projects;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:remove-chats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Chats From Demo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Delete Before 15 Days
            $chatData = Chats::where('created_at', '<', now()->subDays(15))->get();
            if ($chatData->count() > 0) {
                foreach ($chatData as $chat) {
                    $chat->delete();
                }
            }
            Log::info('All chats have been deleted');
        } catch (Exception $e) {
            Log::error('Issue Removing Chats From Demo: ' . $e->getMessage());
        }
    }
}
