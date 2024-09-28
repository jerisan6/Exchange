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
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coin_id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->double('arrival_time');
            $table->text('description')->nullable();
            $table->unsignedBigInteger("last_edit_by")->nullable();
            $table->boolean("status")->default(true);
            $table->timestamps();

            $table->foreign("coin_id")->references("id")->on("coins")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("last_edit_by")->references("id")->on("admins")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('networks');
    }
};
