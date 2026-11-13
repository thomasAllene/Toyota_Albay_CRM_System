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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_id')->nullable();
            $table->unsignedBigInteger('inquiry_type_id');
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('department_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('contact_number');
            $table->string('gender')->nullable();
            $table->date('birthdate')->nullable();
            $table->integer('age')->nullable();
            $table->string('source');
            $table->string('address');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
