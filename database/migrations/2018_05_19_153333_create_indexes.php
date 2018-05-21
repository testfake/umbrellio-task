<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index( 'average_rating' );
            $table->index( 'user_id' );
            $table->index( [ 'ip', 'user_id' ] );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_average_rating_index');
            $table->dropIndex('posts_user_id_index');
            $table->dropIndex('posts_ip_user_id_index');
        });
    }
}
