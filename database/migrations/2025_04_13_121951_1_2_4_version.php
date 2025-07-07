<?php

use App\Models\Chats;
use App\Models\HomepageSection;
use App\Models\PaymentTransaction;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /********************************************************************* */
        // add is_read column to chats table
        if (!Schema::hasColumn('chats', 'is_read')) {
            Schema::table('chats', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->after('message')->comment('by receiver');
            });
        }

        // update all existing chats to is_read = true
        Chats::query()->update(['is_read' => true]);

        /********************************************************************* */

        // add payment_type column to payment_transactions table
        if (!Schema::hasColumn('payment_transactions', 'payment_type')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                // Make Payment gateway true
                $table->string('payment_gateway',191)->nullable(true)->change();
                // Add Payment type
                $table->enum('payment_type', ['online payment', 'bank transfer', 'free'])
                      ->after('payment_gateway')
                      ->comment('Type of payment transaction');
                // Add Reject reason
                $table->text('reject_reason')->nullable()->after('payment_type');
            });

            // Add 'review' option to payment_status enum using DB statement
            DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN payment_status ENUM('success', 'failed', 'pending', 'review', 'rejected') DEFAULT 'pending'");
        }

        // Set existing payments to 'online payment'
        PaymentTransaction::query()->update(['payment_type' => 'online payment']);

        /********************************************************************* */

        // Bank Receipt Files
        if(!Schema::hasTable('bank_receipt_files')){
            Schema::create('bank_receipt_files',function(Blueprint $table){
                $table->id();
                $table->foreignId('payment_transaction_id')->constrained('payment_transactions');
                $table->string('file');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        /********************************************************************* */

        // Table for Homepage Section
        if(!Schema::hasTable('homepage_sections')){
            Schema::create('homepage_sections',function(Blueprint $table){
                $table->id();
                $table->string('title');
                $table->enum('section_type',
                    [
                        'agents_list_section',
                        'articles_section',
                        'categories_section',
                        'faqs_section',
                        'featured_properties_section',
                        'featured_projects_section',
                        'most_liked_properties_section',
                        'most_viewed_properties_section',
                        'nearby_properties_section',
                        'projects_section',
                        'premium_properties_section',
                        'user_recommendations_section',
                        'properties_by_cities_section',
                    ]
                );
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order');
                $table->timestamps();
            });

            // Insert default homepage sections
            $homepageSections = [
                ['id' => 1,     'title' => 'Agents List',              'section_type' => 'agents_list_section',            'is_active' => true, 'sort_order' => 1,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 2,     'title' => 'Articles',                 'section_type' => 'articles_section',               'is_active' => true, 'sort_order' => 2,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 3,     'title' => 'Categories',               'section_type' => 'categories_section',             'is_active' => true, 'sort_order' => 3,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 4,     'title' => 'FAQs',                     'section_type' => 'faqs_section',                   'is_active' => true, 'sort_order' => 4,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 5,     'title' => 'Featured Properties',      'section_type' => 'featured_properties_section',    'is_active' => true, 'sort_order' => 5,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 6,     'title' => 'Featured Projects',        'section_type' => 'featured_projects_section',      'is_active' => true, 'sort_order' => 6,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 7,     'title' => 'Most Liked Properties',    'section_type' => 'most_liked_properties_section',  'is_active' => true, 'sort_order' => 7,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 8,     'title' => 'Most Viewed Properties',   'section_type' => 'most_viewed_properties_section', 'is_active' => true, 'sort_order' => 8,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 9,     'title' => 'Nearby Properties',        'section_type' => 'nearby_properties_section',      'is_active' => true, 'sort_order' => 9,     'created_at' => now(), 'updated_at' => now()],
                ['id' => 10,    'title' => 'Projects',                 'section_type' => 'projects_section',               'is_active' => true, 'sort_order' => 10,    'created_at' => now(), 'updated_at' => now()],
                ['id' => 11,    'title' => 'Premium Properties',       'section_type' => 'premium_properties_section',     'is_active' => true, 'sort_order' => 11,    'created_at' => now(), 'updated_at' => now()],
                ['id' => 12,    'title' => 'User Recommendations',     'section_type' => 'user_recommendations_section',   'is_active' => true, 'sort_order' => 12,    'created_at' => now(), 'updated_at' => now()],
                ['id' => 13,    'title' => 'Properties by Cities',     'section_type' => 'properties_by_cities_section',   'is_active' => true, 'sort_order' => 13,    'created_at' => now(), 'updated_at' => now()],
            ];
            HomepageSection::upsert($homepageSections, ['id']);

            // Update Rent duration to nullable
            if (Schema::hasColumn('propertys', 'rentduration')) {
                Property::where('propery_type', 0)->update(['rentduration' => null]);
            }

        }

        if (Schema::hasColumn('propertys', 'price')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->bigInteger('price')->change();
            });
        }

        $settingsData = array(
            'min_radius_range' => 0,
            'max_radius_range' => 100,
            'timezone' => 'UTC',
            'auto_approve_edited_listings' => 0,
        );

        foreach ($settingsData as $key => $value) {
            Setting::updateOrCreate(['type' => $key], ['data' => $value]);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /********************************************************************* */

        // drop is_read column from chats table
        if (Schema::hasColumn('chats', 'is_read')) {
            Schema::table('chats', function (Blueprint $table) {
                $table->dropColumn('is_read');
            });
        }
        /********************************************************************* */

        // drop payment_type column from payment_transactions table
        if (Schema::hasColumn('payment_transactions', 'payment_type')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                // Make Payment gateway nullable false
                $table->string('payment_gateway')->nullable(false)->change();
                // Drop Payment Type
                $table->dropColumn('payment_type');
                // Drop Reject reason
                $table->dropColumn('reject_reason');
            });

            // Revert payment_status enum back to original options using DB statement
            DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN payment_status ENUM('success', 'failed', 'pending') DEFAULT 'pending'");
        }

        /********************************************************************* */

        // Drop bank_receipt_files table
        Schema::dropIfExists('bank_receipt_files');

        /********************************************************************* */

        // Drop homepage_sections table
        Schema::dropIfExists('homepage_sections');

        /********************************************************************* */

        if (Schema::hasColumn('propertys', 'price')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->decimal('price',10,0)->change();
            });
        }

        /********************************************************************* */

    }
};

