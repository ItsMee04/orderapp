<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 100);
            $table->string('table_no', 5);
            $table->date('order_date');
            $table->time('order_time');
            $table->string('status', 100);
            $table->integer('total')->unsigned();
            $table->unsignedBigInteger('waiters_id')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('waiters_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
