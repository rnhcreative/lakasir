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
        // change expenses.expense_date from date to datetime
        Schema::table('expenses', function (Blueprint $table) {
            $table->dateTime('expense_date')->change();
        });

        // change receivable_payments.date from date to datetime
        Schema::table('receivable_payments', function (Blueprint $table) {
            $table->dateTime('date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // change expenses.expense_date from datetime to date
        Schema::table('expenses', function (Blueprint $table) {
            $table->date('expense_date')->change();
        });

        // change receivable_payments.date from datetime to date
        Schema::table('receivable_payments', function (Blueprint $table) {
            $table->date('date')->change();
        });
    }
};
