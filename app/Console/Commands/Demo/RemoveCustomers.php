<?php

namespace App\Console\Commands\Demo;

use Exception;
use App\Models\Customer;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:remove-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Customers From Demo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Delete Before 15 Days
            $excludeCustomerNumber = ['911234567890'];
            $getCustomerId = Property::where('request_status', 'approved')->groupBy('added_by')->pluck('added_by');
            $customerData = Customer::whereNotIn('id', $getCustomerId)->whereNotIn(function($query) use ($excludeCustomerNumber) {
                $query->whereIn('mobile', $excludeCustomerNumber)->where('logintype', '1');
            })->where('created_at', '<', now()->subDays(15))->get();
            if ($customerData->count() > 0) {
                foreach ($customerData as $customer) {
                    $customer->delete();
                }
            }
            Log::info('All customers have been deleted');
        } catch (Exception $e) {
            dd($e);
            Log::error('Issue Removing Customers From Demo: ' . $e->getMessage());
        }
    }
}
