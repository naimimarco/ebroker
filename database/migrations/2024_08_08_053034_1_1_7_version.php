<?php

use App\Models\Slider;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\table;
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
        // Add Required Column in parameters table
        if (!Schema::hasColumn('parameters', 'is_required')) {
            Schema::table('parameters', function (Blueprint $table) {
                $table->boolean('is_required')->default(0)->after('image');
            });
        }

        // Create Properties Documents Table
        if (!Schema::hasTable('properties_documents')) {
            Schema::create('properties_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->references('id')->on('propertys')->onDelete('cascade');
                $table->string('name',255)->comment('file name with folder');
                $table->string('type')->comment('file type');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Make property id and category id nullable and Add Type , Show Property Details, link and default data Column in Slider Table
        if (!Schema::hasColumn('sliders', 'type')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->enum('type',[1,2,3,4])->after('id')->comment('1 - only image, 2 - category, 3 - property, 4 - link');
                $table->bigInteger('category_id')->nullable(true)->change();
                $table->bigInteger('propertys_id')->nullable(true)->change();
                $table->boolean('show_property_details')->after('propertys_id')->default(false);
                $table->text('link')->after('image')->nullable(true);
                $table->boolean('default_data')->after('show_property_details')->default(false);
            });

            // Update existing records to have type 3 (Property)
            DB::table('sliders')->update(['type' => 3]);

            // Add Slider Default Data
            $slider = new Slider();
            $slider->type = 1;
            $slider->image = "slider-default.png";
            $slider->default_data = true;
            $slider->save();
        }

        // Create Faq Table
        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->text('question');
                $table->text('answer');
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // City Images
        if (!Schema::hasTable('city_images')) {
            Schema::create('city_images', function (Blueprint $table) {
                $table->id();
                $table->string('city')->unique();
                $table->text('image')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });


            $propertiesCityData = Property::select('city')->groupBy('city')->where('status', 1)->pluck('city');
            $citiesData = array();
            foreach ($propertiesCityData as $propertyCity) {
                $citiesData[] = array(
                    'city' => $propertyCity,
                );
            }
            DB::table('city_images')->upsert($citiesData,'city');

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        // Remove Required form parameter table
        if (Schema::hasColumn('parameters', 'is_required')) {
            Schema::table('parameters', function (Blueprint $table) {
                $table->dropColumn('is_required');
            });
        }

        // Remove Properties Documents table
        Schema::dropIfExists('properties_documents');

        // Revert Slider changes
        if (Schema::hasColumn('sliders', 'type')) {
            Schema::table('sliders', function (Blueprint $table) {

                // Delete Default Data entry
                Slider::where('default_data',true)->delete();

                // Reverse other changes
                $table->dropColumn('type');
                $table->bigInteger('category_id')->nullable(false)->change();
                $table->bigInteger('propertys_id')->nullable(false)->change();
                $table->dropColumn('show_property_details');
                $table->dropColumn('link');
                $table->dropColumn('default_data');
            });
        }

        // Remove Faq table
        Schema::dropIfExists('faqs');

        // Remove City Images table
        Schema::dropIfExists('city_images');
    }
};
