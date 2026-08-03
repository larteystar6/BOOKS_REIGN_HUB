<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('sale_uuid');
            $table->uuid('product_uuid');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->decimal('total', 18, 4)->default(0);
            $table->timestamps();

            $table->foreign('sale_uuid')->references('uuid')->on('sales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
