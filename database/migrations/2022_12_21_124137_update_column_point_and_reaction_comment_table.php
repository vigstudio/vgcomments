<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('vgcomment.connection');
        $commentsTable = config('vgcomment.table.comments');

        Schema::connection($connection)->table($commentsTable, function (Blueprint $table) {
            $table->integer('point')->after('parent_id')->nullable()->default(0);
            $table->json('reactions_data')->nullable()->after('point');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('vgcomment.connection'))->table(config('vgcomment.table.comments'), function (Blueprint $table) {
            $table->dropColumn('point');
            $table->dropColumn('reactions_data');
        });
    }
};
