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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('firebase_id');
            $table->string('email');
            $table->string('mobile')->unique();
            $table->text('profile')->nullable();
            $table->text('address');
            $table->text('fcm_id')->nullable();
            $table->tinyText('logintype')->comment('email / gmail / fb / mobile');
            $table->tinyInteger('isActive')->comment('0:No 1:Yes');
            $table->text('api_token')->nullable();
            $table->boolean('notification')->comment('0:disable,1:enable')->default(0);
            $table->boolean('subscription')->default(0);

            $table->timestamps();
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
        Schema::dropIfExists('customers');
        Schema::enableForeignKeyConstraints();
    }
};
