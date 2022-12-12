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

        Schema::connection($connection)
            ->create($commentsTable, function (Blueprint $table) use ($commentsTable) {
                $table->id();
                $table->uuid('uuid')->unique()->index();
                $table->string('page_id')->nullable();
                $table->nullableMorphs('commentable');
                $table->nullableMorphs('responder');
                $table->string('author_name')->nullable();
                $table->string('author_email')->nullable();
                $table->string('author_url')->nullable();
                $table->string('author_ip')->nullable();
                $table->mediumText('user_agent')->nullable();
                $table->string('permalink')->nullable();
                $table->longText('content')->nullable();
                $table->enum('status', ['approved', 'pending', 'spam', 'trash'])->default('approved');
                $table->bigInteger('root_id')->unsigned()->nullable()->index();
                $table->bigInteger('parent_id')->unsigned()->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('root_id')->references('id')->on($commentsTable)->onDelete('cascade');
                $table->foreign('parent_id')->references('id')->on($commentsTable)->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('vgcomment.connection'))->dropIfExists(config('vgcomment.table.comments'));
    }
};
