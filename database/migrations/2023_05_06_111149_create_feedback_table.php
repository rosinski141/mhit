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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('category');
            $table->integer('matches_analyzed');
            $table->string('video_link')->nullable();

            $table->timestamps();


            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign('feedback_user_id_foreign');
        $table->dropIndex('feedback_user_id_index');
        $table->dropColumn('user_id');
        Schema::dropIfExists('feedback');
    }
};
