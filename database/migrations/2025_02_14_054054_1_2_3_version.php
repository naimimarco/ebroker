<?php

use App\Models\Feature;
use App\Models\Package;
use App\Models\Payments;
use App\Models\Projects;
use App\Models\UserPackage;
use Illuminate\Support\Str;
use App\Models\PackageFeature;
use App\Services\HelperService;
use App\Models\UserPackageLimit;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\OldUserPurchasedPackage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // Clear all caches before running migration
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        /**
         * Project Request Status
         * */
        // Add user_side_status and request_status column in property
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'request_status')) {
                $table->enum('request_status',['approved','rejected','pending'])->default('pending')->after('status');
            }
        });

        // Make All projects status inactive
        Projects::query()->where('is_admin_listing','!=',1)->update(['status' => "0"]);
        // Update Admin properties to approved
        Projects::where('is_admin_listing',1)->update(['request_status' => "approved"]);
        /********************************************************************************* */

        // /**
        //  * Package Changes
        //  */

        /** Renames old tables */
        if (Schema::hasTable('packages')) {
            Schema::rename('packages', 'old_packages');
        }
        if (Schema::hasTable('user_purchased_packages')) {
            Schema::rename('user_purchased_packages', 'old_user_purchased_packages');
        }
        if (Schema::hasTable('payments')) {
            Schema::rename('payments', 'old_payments');
        }

        /** New Packages Tables */

        //Features
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Packages
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ios_product_id')->nullable(true)->unique();
            $table->enum('package_type',['free','paid']);
            $table->float('price',10,2)->nullable(true);
            $table->integer('duration')->comment('In Hours');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Package Features
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->foreignId('feature_id')->references('id')->on('features')->onDelete('cascade');
            $table->enum('limit_type',['limited','unlimited']);
            $table->integer('limit')->nullable(true);
            $table->unique(['package_id','feature_id'],'unique_ids');
            $table->timestamps();
            $table->softDeletes();
        });

        // User Packages
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('customers')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // User Packages Limit
        Schema::create('user_package_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_package_id')->references('id')->on('user_packages')->onDelete('cascade');
            $table->foreignId('package_feature_id')->references('id')->on('package_features')->onDelete('cascade');
            $table->integer('total_limit');
            $table->integer('used_limit');
            $table->timestamps();
        });

        // Payment Transactions
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('Customers')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->float('amount',10,2);
            $table->string('payment_gateway',191);
            $table->string('order_id',255)->nullable(true)->comment('Payment Intent Id / Order Id');
            $table->string('transaction_id',255)->nullable(true)->comment('Success Transaction Id');
            $table->enum('payment_status',['success','failed','pending'])->default('pending');
            $table->unique(['payment_gateway','order_id','transaction_id','user_id'],'uniques');
            $table->timestamps();
        });
        /********************************************************************************* */
        /** Project Feature */
        Schema::table('advertisements', function (Blueprint $table) {
            if (Schema::hasColumn('advertisements', 'image')) {
                $table->dropColumn('image');
            }
            $table->enum('for',['property','project'])->comment("Property or Project")->default('property')->after('end_date');
            $table->foreignId('project_id')->nullable(true)->after('property_id')->references('id')->on('projects')->onDelete('cascade');
        });

        /********************************************************************************* */
        /** Add Data */
        $featureData = array(
            ['id' => 1, 'name' => config('constants.FEATURES.PROPERTY_LIST'), 'status' => 1],
            ['id' => 2, 'name' => config('constants.FEATURES.PROPERTY_FEATURE'), 'status' => 1],
            ['id' => 3, 'name' => config('constants.FEATURES.PROJECT_LIST'), 'status' => 1],
            ['id' => 4, 'name' => config('constants.FEATURES.PROJECT_FEATURE'), 'status' => 1],
            ['id' => 5, 'name' => config('constants.FEATURES.MORTGAGE_CALCULATOR_DETAIL'), 'status' => 1],
            ['id' => 6, 'name' => config('constants.FEATURES.PREMIUM_PROPERTIES'), 'status' => 1],
            ['id' => 7, 'name' => config('constants.FEATURES.PROJECT_ACCESS'), 'status' => 1],
        );
        Feature::upsert($featureData,['id'],['name','status']);

        DB::beginTransaction();
        /********************************************************************************* */
        /** Migrate Old Packages System to New */
        $this->migrateOldPackagesData();

        DB::commit();

        /********************************************************************************* */
        /** Update Paystack details in env */
        $paystackDetails = HelperService::getMultipleSettingData(array('paystack_public_key','paystack_secret_key'));
        HelperService::changeEnv([
            'PAYSTACK_PUBLIC_KEY'  => $paystackDetails['paystack_public_key'] ?? '',
            'PAYSTACK_SECRET_KEY'  => $paystackDetails['paystack_secret_key'] ?? '',
            'PAYSTACK_PAYMENT_URL' => "https://api.paystack.co"
        ]);
        /********************************************************************************* */



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        /********************************************************************************* */
        /**
         * Project Request Status
         * */
        // Drop request status
        if (Schema::hasColumn('projects', 'request_status')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('request_status');
            });
        }
        /********************************************************************************* */
        /**
         * Package Changes
         * */
        Schema::dropIfExists('user_package_limits');
        Schema::dropIfExists('user_packages');
        Schema::dropIfExists('package_features');
        if (Schema::hasTable('packages')) {
            Schema::rename('packages', 'new_packages');
        }
        Schema::dropIfExists('new_packages');
        Schema::dropIfExists('features');
        Schema::dropIfExists('payment_transactions');
        /********************************************************************************* */

        /**
         * Project Feature Changes
         * */

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['for', 'project_id']);
        });

        /********************************************************************************* */


        Schema::enableForeignKeyConstraints();
    }

    private function migrateOldPackagesData(){

        $oldUserPackageData = OldUserPurchasedPackage::with('package','customer:id')->get()->filter(function($oldUserPackage){
            return !empty($oldUserPackage->package) && !empty($oldUserPackage->customer);
        });
        if(collect($oldUserPackageData)->isNotEmpty()){
            foreach ($oldUserPackageData as $oldUserPackage) {
                if(collect($oldUserPackage)->isNotEmpty()){
                    $oldPackage = $oldUserPackage->package;

                    // Update Or Create Package
                    $newPackageData = Package::updateOrCreate(
                        array(
                            'id' => $oldPackage->id
                        ),
                        array(
                            'ios_product_id'    => $oldPackage->ios_product_id,
                            'name'              => $oldPackage->name,
                            'package_type'      => !empty($oldPackage->price) ? 'paid' : 'free',
                            'price'             => !empty($oldPackage->price) ? $oldPackage->price : null,
                            'duration'          => $oldPackage->duration * 24
                        )
                    );

                    // New Package Feature Array
                    $newPackageFeatureArray = array(
                        [
                            'package_id' => $newPackageData->id,
                            'feature_id' => HelperService::getFeatureId('mortgage_calculator_detail'),
                            'limit_type' => 'unlimited',
                            'limit'      => null,
                            'used_limit' => null
                        ],
                        [
                            'package_id' => $newPackageData->id,
                            'feature_id' => HelperService::getFeatureId('premium_properties'),
                            'limit_type' => 'unlimited',
                            'limit'      => null,
                            'used_limit' => null
                        ],
                        [
                            'package_id' => $newPackageData->id,
                            'feature_id' => HelperService::getFeatureId('project_access'),
                            'limit_type' => 'unlimited',
                            'limit'      => null,
                            'used_limit' => null
                        ],
                    );

                    if($oldPackage->type == 'product_listing'){
                        // IF property Limit
                        if($oldPackage->property_limit != 0){
                            $propertyLimitData = array(
                                [
                                    'package_id' => $newPackageData->id,
                                    'feature_id' => HelperService::getFeatureId('property_list'),
                                    'limit_type' => $oldPackage->property_limit != null ? 'limited' : 'unlimited',
                                    'limit'      => $oldPackage->property_limit != null ? $oldPackage->property_limit : null,
                                    'used_limit' => $oldUserPackage->used_limit_for_property
                                ]
                            );
                            $projectLimitData = array(
                                [
                                    'package_id' => $newPackageData->id,
                                    'feature_id' => HelperService::getFeatureId('project_list'),
                                    'limit_type' => $oldPackage->property_limit != null ? 'limited' : 'unlimited',
                                    'limit'      => $oldPackage->property_limit != null ? $oldPackage->property_limit : null,
                                    'used_limit' => $oldUserPackage->used_limit_for_property
                                ]
                            );
                            $newPackageFeatureArray = array_merge($projectLimitData,$propertyLimitData,$newPackageFeatureArray);
                        }
                        // IF Advertisement Limit
                        if($oldPackage->advertisement_limit != 0){
                            $advertisementLimitData = array(
                                [
                                    'package_id'    => $newPackageData->id,
                                    'feature_id'    => HelperService::getFeatureId('property_feature'),
                                    'limit_type'    => $oldPackage->advertisement_limit != null ? 'limited' : 'unlimited',
                                    'limit'         => $oldPackage->advertisement_limit != null ? $oldPackage->advertisement_limit : null,
                                    'used_limit'    => $oldUserPackage->used_limit_for_advertisement
                                ]
                            );
                            $newPackageFeatureArray = array_merge($advertisementLimitData,$newPackageFeatureArray);
                        }

                        // Add Package Feature and User Package, create user package limit array
                        foreach ($newPackageFeatureArray as $key => $data) {

                            // Update Or Create Package Feature
                            $packageFeatureData = PackageFeature::updateOrCreate(
                                array(
                                    'package_id' => $data['package_id'],
                                    'feature_id' => $data['feature_id']
                                ),
                                array(
                                    'limit_type' => $data['limit_type'],
                                    'limit'      => $data['limit']
                                )
                            );

                            // Update Or Create User Package
                            $userPackageData = UserPackage::updateOrCreate(
                                array(
                                    'user_id'       => $oldUserPackage->modal_id,
                                    'package_id'    => $newPackageData->id,
                                ),
                                array(
                                    'start_date'    => $oldUserPackage->start_date,
                                    'end_date'      => $oldUserPackage->end_date
                                ),
                            );

                            if($data['limit'] != null){
                                // Update or Create User Package Limit
                                UserPackageLimit::updateOrCreate(
                                    array(
                                        'user_package_id' => $userPackageData->id,
                                        'package_feature_id' => $packageFeatureData->id
                                    ),
                                    array(
                                        'total_limit' => $data['limit'],
                                        'used_limit' => $data['used_limit']
                                        )
                                );
                            }
                        }
                    }else{
                        foreach ($newPackageFeatureArray as $key => $data) {

                            // Update Or Create Package Feature
                            $packageFeatureData = PackageFeature::updateOrCreate(
                                array(
                                    'package_id' => $data['package_id'],
                                    'feature_id' => $data['feature_id']
                                ),
                                array(
                                    'limit_type' => $data['limit_type'],
                                    'limit'      => $data['limit']
                                )
                            );

                            // Update Or Create User Package
                            UserPackage::updateOrCreate(
                                array(
                                    'user_id'       => $oldUserPackage->modal_id,
                                    'package_id'    => $newPackageData->id,
                                ),
                                array(
                                    'start_date'    => $oldUserPackage->start_date,
                                    'end_date'      => $oldUserPackage->end_date
                                ),
                            );
                        }
                    }
                }
            }
        }
        $oldPaymentTransactions = Payments::with('package:id','customer:id')->get()->filter(function($payment){
            return !empty($payment->package) && !empty($payment->customer);
        });
        if(collect($oldPaymentTransactions)->isNotEmpty()){
            foreach ($oldPaymentTransactions as $key => $oldTransactionData) {
                if(!empty($oldTransactionData)){
                    PaymentTransaction::updateOrCreate(
                        array(
                            'payment_gateway'   => Str::ucfirst($oldTransactionData->payment_gateway),
                            'user_id'           => $oldTransactionData->customer_id,
                            'transaction_id'    => $oldTransactionData->transaction_id
                        ),
                        array(
                            'package_id'        => $oldTransactionData->package_id,
                            'amount'            => $oldTransactionData->amount,
                            'payment_status'    => $oldTransactionData->status == 1 ? 'success' : 'failed',
                        )
                    );
                }
            }
        }
        return true;
    }
};