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
        Schema::create('inquiry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_type_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('transaction');
            $table->string('category');
            $table->integer('quantity')->nullable();
            $table->longText('remarks')->nullable();
            $table->string('date')->nullable(); //monthname day
            $table->string('status_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('status_updated_by')->nullable();
            $table->date('status_updated_at')->nullable();
            $table->boolean('is_dispute')->default('0');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry');
    }
};
