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
        Schema::create('backup_databases', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable();
            $table->boolean('is_uploaded_on_local')->default(false);
            $table->boolean('is_uploaded_on_cloud')->default(false);
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_databases');
    }
};
