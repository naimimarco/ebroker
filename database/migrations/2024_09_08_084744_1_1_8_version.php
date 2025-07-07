<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Login methods in settings table
        $data = array('number_with_otp_login' => 1, 'social_login' => 1,'distance_option' => 'km', 'otp_service_provider' => 'firebase');
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['type' => $key], ['data' => $value]);
        }

        // Change Distance Column datatype to float
        Schema::table('assigned_outdoor_facilities', function (Blueprint $table) {
            $table->float('distance',10,1)->change();
        });

        // New table to store otps of numbers
        Schema::create('number_otps', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('otp');
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });

        // Rename the firebase id column to auth id
        if (Schema::hasColumn('customers', 'firebase_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->renameColumn('firebase_id','auth_id');
            });
        }

        if (Schema::hasColumn('propertys', 'added_by')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->integer('added_by')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the number otp table
        Schema::dropIfExists('number_otps');

        // Rename the auth id column to firebase id
        if (Schema::hasColumn('customers', 'auth_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->renameColumn('auth_id','firebase_id');
            });
        }
    }
};