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
        // drop the constrained price_unit_id column on cart_items table
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['price_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('price_unit_id')->nullable()->constrained();
        });
    }
};
