<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /** It's 1.2.1 Version */

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add is admin listing column and make added by nullable
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('added_by')->nullable(true)->change();
            if (!Schema::hasColumn('projects', 'is_admin_listing')) {
                $table->boolean('is_admin_listing')->default(false);
            }
            $table->string('type')->comment('under_construction,upcoming')->change();
            $table->string('video_link')->nullable(true)->change();
        });

        Schema::create('blocked_chat_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('by_user_id')->comment('Block by user')->nullable(true)->references('id')->nullable(true)->on('customers')->onDelete('cascade');
            $table->boolean('by_admin')->comment('Block by admin')->default(false);
            $table->foreignId('user_id')->comment('Block to user')->nullable(true)->references('id')->nullable(true)->on('customers')->onDelete('cascade');
            $table->boolean('admin')->comment('Block to admin')->default(false);
            $table->longText('reason')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add text of property submission
        $data = array('text_property_submission' => 'Your property has been added and is pending review. The admin will enable it once the review is complete.');
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['type' => $key], ['data' => $value]);
        }

        // Add Web URL
        $getWebUrl = Setting::where('type','paypal_web_url')->pluck('data')->first();
        if(!empty($getWebUrl)){
            Setting::updateOrCreate(['type' => 'web_url'], ['data' => $getWebUrl]);
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // remove is admin listing column
        if (Schema::hasColumn('projects', 'is_admin_listing')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('is_admin_listing');
            });
        }
        Schema::dropIfExists('blocked_chat_users');
    }
};
