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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_number')->nullable();
            $table->unsignedBigInteger('inquiry_id')->nullable();
            $table->unsignedBigInteger('application_id')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('reservation_transaction_status')->nullable();
            $table->string('reservation_status')->default('none');
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->date('application_transaction_date')->nullable();
            $table->date('transaction_updated_date')->nullable();
            $table->date('reservation_date')->nullable();
            $table->date('released_date')->nullable();
            $table->longText('released_remarks')->nullable();
            $table->string('status')->nullable();
            $table->longText('lto_remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
