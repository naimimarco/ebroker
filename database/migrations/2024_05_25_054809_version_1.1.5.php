<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('propertys', function (Blueprint $table) {
            $table->decimal('price', 36, 2)->change();
        });

        Schema::table('usertokens', function (Blueprint $table) {
            // Remove duplicate entries based on fcm_id
            DB::statement(
                "DELETE usertokens
                FROM usertokens
                LEFT JOIN (
                    SELECT MAX(id) as id
                    FROM usertokens
                    GROUP BY fcm_id
                ) as keep_ids ON usertokens.id = keep_ids.id
                WHERE keep_ids.id IS NULL"
            );

            // Add a unique constraint to the fcm_id column
            $table->string('fcm_id')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('propertys', function (Blueprint $table) {
            $table->double('price')->change();
        });
        Schema::table('usertokens', function (Blueprint $table) {
            $table->dropUnique('usertokens_fcm_id_unique');
        });
    }
};
