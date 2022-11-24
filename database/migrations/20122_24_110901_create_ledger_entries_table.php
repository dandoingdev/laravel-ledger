<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ledgerable_type', 255);
            $table->uuid('ledgerable_id');
            $table->string('money_to')->nullable();
            $table->string('money_from')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('credit')->default(false);
            $table->float('amount', 8, 2);
            $table->string('currency')->nullable();
            $table->float('balance', 8, 2);
            $table->string('balance_currency')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('ledger_entries');
    }
};
