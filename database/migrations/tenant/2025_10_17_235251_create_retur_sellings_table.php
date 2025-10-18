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
        Schema::create('retur_sellings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selling_detail_id')->constrained('selling_details');
            $table->foreignId('new_product_id')->nullable()->constrained('products');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->integer('qty');
            $table->double('refund_amount')->default(0);
            $table->double('additional_amount')->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_sellings');
    }
};
