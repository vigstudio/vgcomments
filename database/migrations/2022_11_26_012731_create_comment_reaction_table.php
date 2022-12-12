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
        $reactionsTable = config('vgcomment.table.reactions');

        Schema::connection($connection)
                ->create($reactionsTable, function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->unique()->index();
                    $table->bigInteger('comment_id')->unsigned()->nullable()->index();
                    $table->uuid('comment_uuid')->nullable()->index();
                    $table->string('type')->nullable();
                    $table->morphs('reactable');
                    $table->timestamps();

                    $table->foreign('comment_id')->references('id')->on(config('vgcomment.table.comments'))->onDelete('cascade');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('vgcomment.connection'))->dropIfExists(config('vgcomment.table.reactions'));
    }
};
