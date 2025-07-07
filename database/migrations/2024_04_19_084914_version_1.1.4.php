<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SEO COLUMNS MAKING NULLABLE
        Schema::table('propertys', function (Blueprint $table) {
            $table->text('meta_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
            $table->string('meta_image')->nullable()->change();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->text('meta_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
            $table->string('meta_image')->nullable()->change();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->text('meta_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
            $table->string('meta_image')->nullable()->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->text('meta_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
            $table->string('meta_image')->nullable()->change();
        });


        // ADD Slug id column in projects table if not exists
        if (!Schema::hasColumn('projects', 'slug_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('slug_id')->unique();
            });
        }else{
            Schema::table('projects', function (Blueprint $table) {
                $table->string('slug_id')->unique()->change();
            });
        }

        // ADD categories_id column in projects table if not exists
        if (!Schema::hasColumn('projects', 'category_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        // ADD status column in projects table if not exists
        if (!Schema::hasColumn('projects', 'status')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('status')->default(0);
            });
        }


        // ADDING propertys_inquiry table if not exists
        if(!Schema::hasTable('propertys_inquiry')){
            Schema::create('propertys_inquiry', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('propertys_id')->unsigned();
                $table->bigInteger('customers_id')->unsigned();
                $table->tinyInteger('status')->default(0)->comment('0 : Pending 1:Accept  2: Complete 3:Cancle');
                $table->foreign('propertys_id')->references('id')->on('propertys')->onDelete('cascade');
                $table->foreign('customers_id')->references('id')->on('customers')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // ADDING total_click column in propertys if not exists
        Schema::table('propertys',function (Blueprint $table){
            if(!Schema::hasColumn('propertys','total_click')){
                $table->bigInteger('total_click')->default(0);
            }
        });

        // ADDING total_click column in projects if not exists
        Schema::table('projects',function (Blueprint $table){
            if(!Schema::hasColumn('projects','total_click')){
                $table->bigInteger('total_click')->default(0);
            }
        });

        // Drop unique constraint on 'mobile'
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_mobile_unique');
        });
        // Add unique constraint on 'email', 'mobile', and 'logintype'
        Schema::table('customers', function (Blueprint $table) {
            $table->string('logintype')->change();
            $table->unique(['email', 'mobile', 'logintype'], 'unique_ids');
        });

        // Add unique constraint on language's code
        Schema::table('languages', function (Blueprint $table) {
            $table->unique('code');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        // REVERTING SEO COLUMNS MAKING NULLABLE
        Schema::table('propertys', function (Blueprint $table) {
            $table->text('meta_title')->nullable(false)->change();
            $table->text('meta_description')->nullable(false)->change();
            $table->text('meta_keywords')->nullable(false)->change();
            $table->string('meta_image')->nullable(false)->change();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->text('meta_title')->nullable(false)->change();
            $table->text('meta_description')->nullable(false)->change();
            $table->text('meta_keywords')->nullable(false)->change();
            $table->string('meta_image')->nullable(false)->change();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->text('meta_title')->nullable(false)->change();
            $table->text('meta_description')->nullable(false)->change();
            $table->text('meta_keywords')->nullable(false)->change();
            $table->string('meta_image')->nullable(false)->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->text('meta_title')->nullable(false)->change();
            $table->text('meta_description')->nullable(false)->change();
            $table->string('meta_image')->nullable(false)->change();
        });


        // DROP slug_id column from projects table if exists
        if (Schema::hasColumn('projects', 'slug_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('slug_id');
            });
        }

        // DROP category_id column from projects table if exists
        if (Schema::hasColumn('projects', 'category_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropForeign(['category_id']); // drop foreign key
                $table->dropColumn('category_id');
            });
        }

        // DROP status column from projects table if exists
        if (Schema::hasColumn('projects', 'status')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }



        // Drop propertys_inquiry if exists
        Schema::dropIfExists('propertys_inquiry');

        // Drop total_click Column in propertys if exists
        Schema::table('propertys',function (Blueprint $table){
            if(Schema::hasColumn('propertys','total_click')){
                $table->dropColumn('total_click');
            }
        });

        // Drop total_click Column in projects if exists
        Schema::table('projects',function (Blueprint $table){
            if(Schema::hasColumn('projects','total_click')){
                $table->dropColumn('total_click');
            }
        });

        // Drop unique constraint on 'email', 'mobile', and 'logintype'
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('unique_ids');
        });

        // Add back the unique constraint on 'mobile'
        Schema::table('customers', function (Blueprint $table) {
            $table->unique('mobile', 'customers_mobile_unique');
        });

        // Drop Language Code Unique Constraint
        Schema::table('languages', function (Blueprint $table) {
            $table->dropUnique('languages_code_unique');
        });


        Schema::enableForeignKeyConstraints();
    }
};
