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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->integer('year_model');
            $table->unsignedBigInteger('vehicle_id');
            $table->string('CS_number');
            $table->date('actual_invoice_date');
            $table->date('delivery_date')->nullable();
            $table->string('invoice_number');
            $table->integer('age')->nullable();
            $table->string('status')->default('Available');
            $table->string('CS_number_status')->default('Available');
            $table->string('incoming_status')->default('Invoice');
            $table->string('tag')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('inventory');
    }
};
