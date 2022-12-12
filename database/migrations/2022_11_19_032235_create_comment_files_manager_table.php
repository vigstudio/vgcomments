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
        $filesTable = config('vgcomment.table.files');

        Schema::connection($connection)
            ->create($filesTable, function (Blueprint $table) {
                $table->id();
                $table->string('hash');
                $table->uuid('uuid')->unique()->index();
                $table->bigInteger('comment_id')->unsigned()->nullable()->index();
                $table->uuid('comment_uuid')->nullable()->index();
                $table->string('name');
                $table->string('path');
                $table->string('file_name');
                $table->string('mime')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('disk');
                $table->unsignedBigInteger('size');
                $table->timestamps();
                $table->softDeletes();
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
        Schema::connection(config('vgcomment.connection'))->dropIfExists(config('vgcomment.table.files'));
    }
};
