<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Stripe\Tax\Settings;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Slug id column in Users Table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'slug_id')) {
                $table->string('slug_id')->after('name')->comment('Slug of name');
            }
        });

        // Add Slug to every Name of Users
        $userUpdateArray = array();
        $users = User::get();
        foreach ($users as $user) {
            if ($user->name) {
                $slug = Str::slug($user->name);
                $counter = 1;

                while (DB::table('users')->where('slug_id', $slug)->exists()) {
                    $slug = $slug . '-' . $counter;
                    $counter++;
                }
                $userUpdateArray[] = array(
                    'id' => $user->id,
                    'slug_id' => $slug
                );
            }
        }
        if (!empty($userUpdateArray)) {
            DB::table('users')->upsert($userUpdateArray, 'id');
        }


        // Add Slug id column in Customers Table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'slug_id')) {
                $table->string('slug_id')->after('name')->comment('Slug of name');
            }
        });

        // Add Slug to every Name of Customers
        $customerUpdateArray = array();
        $customers = Customer::get();
        foreach ($customers as $customer) {
            if ($customer->name) {
                $slug = Str::slug($customer->name);
                $counter = 1;

                while (DB::table('users')->where('slug_id', $slug)->exists() || DB::table('customers')->where('slug_id', $slug)->exists()) {
                    $slug = $slug . '-' . $counter;
                    $counter++;
                }
                $customerUpdateArray[] = array(
                    'id' => $customer->id,
                    'slug_id' => $slug
                );
            }
        }
        if (!empty($customerUpdateArray)) {
            DB::table('customers')->upsert($customerUpdateArray, 'id');
        }


        if (Schema::hasColumn('customers', 'pintrest_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('pintrest_id');
                $table->string('youtube_id')->after('instagram_id')->nullable();
            });
        }else{
            if (!Schema::hasColumn('customers', 'youtube_id')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->string('youtube_id')->after('instagram_id')->nullable();
                });
            }
        }

        if (Schema::hasColumn('propertys', 'threeD_image')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->renameColumn('threeD_image', 'three_d_image');
            });
        }

        if (Schema::hasColumn('parameters', 'type_values')) {
            Schema::table('parameters', function (Blueprint $table) {
                $table->text('type_values')->change();
            });
        }

        if (Schema::hasColumn('propertys', 'price')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->decimal('price',10,0)->change();
            });
        }

        // Remove the Old Setting data
        Setting::where('type','sell_background')->delete();

        // Add New Settings Data
        $addDataInSettings = array(
            'category_background' => '#087C7C14',
            'sell_web_color' => '#14B8DCFF',
            'sell_web_background_color' => '#14B8DC1F',
            'rent_web_color' => '#E48D18FF',
            'rent_web_background_color' => '#E48D181F'
        );
        foreach ($addDataInSettings as $key => $data) {
            Setting::updateOrCreate(['type' => $key], ['data' => $data]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'slug_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('slug_id');
            });
        }

        if (Schema::hasColumn('customers', 'slug_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('slug_id');
            });
        }

        if (Schema::hasColumn('customers', 'youtube_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('youtube_id');
                $table->string('pintrest_id')->after('instagram_id')->nullable();
            });
        }else{
            if (!Schema::hasColumn('customers', 'pintrest_id')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->string('pintrest_id')->after('instagram_id')->nullable();
                });
            }
        }


        if (Schema::hasColumn('propertys', 'three_d_image')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->renameColumn('three_d_image', 'threeD_image');
            });
        }

        if (Schema::hasColumn('parameters', 'type_values')) {
            Schema::table('parameters', function (Blueprint $table) {
                $table->string('type_values')->change();
            });
        }

        if (Schema::hasColumn('propertys', 'price')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->decimal('price',10,2)->change();
            });
        }

        // Add Setting data
        Setting::updateOrCreate(['type' => 'sell_background', 'data' => "#E8AA42"]);

        // Remove Settings Data
        $settingTypes = array(
            'category_background',
            'sell_web_color',
            'sell_web_background_color',
            'rent_web_color',
            'rent_web_background_color'
        );
        Setting::whereIn('type',$settingTypes)->delete();
    }
};
