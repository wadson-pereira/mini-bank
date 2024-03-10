<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->decimal('amount', 10, 2)->unsigned();
            $table->dateTime('scheduled_for')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();
            $table->foreign('sender_id')->on('account')->references('id');
            $table->foreign('receiver_id')->on('account')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transaction');
    }
};
