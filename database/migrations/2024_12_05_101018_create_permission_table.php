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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('permission_name');
            $table->longText('permission_description')->nullable();
            $table->timestamps(0); // Enable timestamps with precision
            $table->softDeletes();
        });

        // Pivot table for role_permission
        Schema::create('permission_usertype', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usertype_id');
            $table->foreign('usertype_id')->references('id')->on('usertypes');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps(0); // Enable timestamps with precision
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_role');
    }
};
