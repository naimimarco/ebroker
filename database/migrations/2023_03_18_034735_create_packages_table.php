<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('duration')->comment('Months');
            $table->double('price');
            $table->integer('status')->comment('0:off,1:onn');
            $table->integer('property_limit')->nullable();
            $table->integer('advertisement_limit')->nullable();

            $table->timestamps();
        });
        $data = [[
            'id' => 1,
            'name' => 'Trial Package',
            'duration' => '30',
            'status' => '1',
            'property_limit' => 10,
            'advertisement_limit' => 10,
            'status' => '1',
        ]];
        DB::table('packages')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
};
