<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZodiacSignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zodiac_signs', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('uri_id');
            $table->string('name');
            $table->tinyInteger('total_rank');
            $table->string('total_describe');
            $table->tinyInteger('love_rank');
            $table->string('love_describe');
            $table->tinyInteger('job_rank');
            $table->string('job_describe');
            $table->tinyInteger('money_rank');
            $table->string('money_describe');
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
        Schema::dropIfExists('zodiac_signs');
    }
}
