<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public $backupProductData;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /** Take backup of IOS product ID in other table and make IOS Product ID's data All Null with Unique */
        if (Schema::hasColumn('packages', 'ios_product_id')) {
            Schema::create('packages_backup', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->references('id')->on('packages')->onDelete('cascade');
                $table->string('ios_product_id');
                $table->timestamps();
            });

            // Backup the data
            $backupProductData = DB::table('packages')->whereNotNull('ios_product_id')->select('id', 'ios_product_id')->get();
            foreach ($backupProductData as $data) {
                DB::table('packages_backup')->insert([
                    'package_id' => $data->id,
                    'ios_product_id' => $data->ios_product_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Backup the data
            $this->backupProductData = DB::table('packages')->whereNotNull('ios_product_id')->select('id', 'ios_product_id')->get();

            // Make All Data Null
            DB::table('packages')->whereNotNull('ios_product_Id')->update(['ios_product_Id' => null]);

            // Make IOS Product ID unique
            Schema::table('packages', function(Blueprint $table){
                $table->string('ios_product_id')->nullable()->unique()->change();
            });
        }
        /**************************************************************************/

        /** User Verification Tables */

        // Verify Customer Form
        Schema::create('verify_customer_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('field_type',['text','number','radio','checkbox','dropdown','textarea','file']);
            $table->integer('rank')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Verify Customer Form Values
        Schema::create('verify_customer_form_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verify_customer_form_id')->references('id')->on('verify_customer_forms')->onDelete('cascade');
            $table->text('value');
            $table->timestamps();
            $table->softDeletes();
        });

        // Verify Customer
        Schema::create('verify_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('customers')->onDelete('cascade');
            $table->enum('status',['failed','success','pending'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // Verify Customer
        Schema::create('verify_customer_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verify_customer_id')->references('id')->on('verify_customers')->onDelete('cascade');
            $table->foreignId('verify_customer_form_id')->references('id')->on('verify_customer_forms')->onDelete('cascade');
            $table->text('value');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['verify_customer_id','verify_customer_form_id'],'unique_id');
        });
        /**************************************************************************/

        Schema::table('notification',function(Blueprint $table){
            $table->bigInteger('propertys_id')->nullable(true)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /** Remove IOS Product ID's Unique, and restore the backup from the temp table*/
        if (Schema::hasColumn('packages', 'ios_product_id')) {
            // Remove the unique constraint
            Schema::table('packages', function (Blueprint $table) {
                $table->dropUnique(['ios_product_id']);
            });

            // Restore the data from the temporary table
            if (Schema::hasTable('packages_backup')) {
                // Get Data
                $backupProductData = DB::table('packages_backup')->get();

                // Update the IOS product id to that id from backup table
                foreach ($backupProductData as $data) {
                    DB::table('packages')->where('id', $data->package_id)->update(['ios_product_id' => $data->ios_product_id]);
                }

                // Drop the temporary table
                Schema::dropIfExists('packages_backup');
            }
        }
        /**************************************************************************/

        /** User Verification Tables */

        Schema::dropIfExists('verify_customer_forms');
        Schema::dropIfExists('verify_customer_form_values');
        Schema::dropIfExists('verify_customers');
        Schema::dropIfExists('verify_customer_values');

        /**************************************************************************/

        Schema::table('notification',function(Blueprint $table){
            $table->bigInteger('propertys_id')->nullable(false)->default(0)->change();
        });

    }
};
