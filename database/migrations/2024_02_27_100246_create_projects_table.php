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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug_id');
            $table->text('description');
            $table->string('meta_title');
            $table->text('meta_description');
            $table->text('meta_keywords');
            $table->string('meta_image');

            $table->string('image');
            $table->string('video_link');
            $table->string('location');
            $table->string('latitude');
            $table->string('longitude');

            $table->string('city');
            $table->string('state');
            $table->string('country');

            $table->string('type')->comment('under_process,upcoming');

            $table->timestamps();
            $table->foreignId('added_by')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
