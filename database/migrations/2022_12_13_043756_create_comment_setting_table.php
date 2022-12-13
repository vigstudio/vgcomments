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
        $settingTable = config('vgcomment.table.settings');

        Schema::connection($connection)
            ->create($settingTable, function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique()->index();
                $table->string('key')->unique();
                $table->text('value');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('vgcomment.connection'))->dropIfExists(config('vgcomment.table.settings'));
    }
};
