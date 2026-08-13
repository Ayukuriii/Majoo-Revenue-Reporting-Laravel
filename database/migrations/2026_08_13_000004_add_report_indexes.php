<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Report-oriented indexes. Composites are added before dropping the
     * single-column merchant_id index so the foreign key stays covered.
     */
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->unique('user_id');
            $table->dropIndex(['user_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['merchant_id', 'created_at']);
            $table->index(['merchant_id', 'outlet_id', 'created_at']);
            $table->dropIndex(['merchant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('merchant_id');
            $table->dropIndex(['merchant_id', 'outlet_id', 'created_at']);
            $table->dropIndex(['merchant_id', 'created_at']);
        });

        Schema::table('merchants', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropUnique(['user_id']);
        });
    }
};
