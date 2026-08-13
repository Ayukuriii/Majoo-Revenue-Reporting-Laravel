<?php

use App\Models\Merchant;
use App\Models\Outlet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * bill_total uses decimal(15,2) instead of the assignment's double so money
     * is stored with exact cents (avoids binary floating-point rounding).
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Merchant::class)->index()->constrained();
            $table->foreignIdFor(Outlet::class)->index()->constrained();
            $table->decimal('bill_total', 15, 2);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
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
