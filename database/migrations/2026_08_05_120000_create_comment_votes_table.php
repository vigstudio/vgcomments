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
        $votesTable = config('vgcomment.table.votes');

        Schema::connection($connection)->create($votesTable, function (Blueprint $table) use ($commentsTable) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('comment_id')->nullable()->index();
            $table->uuid('comment_uuid')->nullable()->index();
            $table->tinyInteger('value'); // 1 = upvote, -1 = downvote
            $table->morphs('voterable');
            $table->timestamps();

            $table->unique(['comment_id', 'voterable_type', 'voterable_id'], 'vgcomment_votes_unique_voter');
            $table->foreign('comment_id')->references('id')->on($commentsTable)->onDelete('cascade');
        });

        $schema = Schema::connection($connection);

        if (! $schema->hasColumn($commentsTable, 'upvotes')) {
            $schema->table($commentsTable, function (Blueprint $table) {
                $table->unsignedInteger('upvotes')->default(0)->after('point');
            });
        }

        if (! $schema->hasColumn($commentsTable, 'downvotes')) {
            $schema->table($commentsTable, function (Blueprint $table) {
                $table->unsignedInteger('downvotes')->default(0)->after('upvotes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('vgcomment.connection');
        $commentsTable = config('vgcomment.table.comments');
        $votesTable = config('vgcomment.table.votes');
        $schema = Schema::connection($connection);

        $schema->table($commentsTable, function (Blueprint $table) use ($schema, $commentsTable) {
            if ($schema->hasColumn($commentsTable, 'downvotes')) {
                $table->dropColumn('downvotes');
            }
            if ($schema->hasColumn($commentsTable, 'upvotes')) {
                $table->dropColumn('upvotes');
            }
        });

        $schema->dropIfExists($votesTable);
    }
};
