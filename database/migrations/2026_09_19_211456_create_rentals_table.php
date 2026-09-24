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
      Schema::create('rentals', function (Blueprint $table) {
    $table->id();
    $table->string('rental_code')->unique();
    $table->foreignId('user_id')->constrained()->restrictOnDelete();
    $table->string('customer_name');
    $table->string('customer_phone', 20);
    $table->string('customer_email');
    $table->text('customer_address');
    $table->date('pickup_date');
    $table->date('return_date');
    $table->unsignedSmallInteger('total_days');
    $table->unsignedBigInteger('total_price');
    $table->string('status', 20)->default('pending')->index();
    $table->text('admin_note')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
