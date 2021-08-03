<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_attributes', function (Blueprint $table) {
            $table->BigIncrements('id');
            $table->unsignedBigInteger('search_history_id');
            $table->unsignedBigInteger('attribute_id');
            $table->bigInteger('min')->nullable();
            $table->bigInteger('max')->nullable();
            $table->string('value',60)->nullable();
            $table->foreign('search_history_id')->references('id')->on('search_histories');
            $table->foreign('attribute_id')->references('id')->on('attributes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_attributes');
    }
}
