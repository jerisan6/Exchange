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
        Schema::create('outside_wallet_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('network_id');
            $table->string('slug')->unique();
            $table->string('public_address');
            $table->text('desc',500)->nullable();
            $table->text('input_fields',1000)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("network_id")->references("id")->on("networks")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outside_wallet_addresses');
    }
};
