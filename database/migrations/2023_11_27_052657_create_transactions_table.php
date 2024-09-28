<?php

use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum("type",[
                PaymentGatewayConst::BUY_CRYPTO,
                PaymentGatewayConst::SELL_CRYPTO,
                PaymentGatewayConst::WITHDRAW_CRYPTO,
                PaymentGatewayConst::EXCHANGE_CRYPTO,
                PaymentGatewayConst::TYPEADDSUBTRACTBALANCE,
            ]);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_wallet_id')->nullable();
            $table->unsignedBigInteger('payment_gateway_id')->nullable();
            $table->string('trx_id')->comment('Transaction ID');
            $table->decimal('amount', 28, 16)->nullable();
            $table->decimal('percent_charge', 28, 16)->nullable();
            $table->decimal('fixed_charge', 28, 16)->nullable();
            $table->decimal('total_charge', 28, 16)->nullable();
            $table->decimal('total_payable', 28, 16)->nullable();
            $table->decimal('available_balance', 28, 16)->nullable();
            $table->string('currency_code')->nullable();
            $table->string('remark')->nullable();
            $table->text('details')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('callback_ref')->nullable();
            $table->tinyInteger('status')->default(0)->comment("1: Pending, 2: Confirm Payment, 3: Canceled, 4: Rejected");
            $table->timestamps();

            
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("user_wallet_id")->references("id")->on("user_wallets")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("payment_gateway_id")->references("id")->on("payment_gateways")->onDelete("cascade")->onUpdate("cascade");
            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
