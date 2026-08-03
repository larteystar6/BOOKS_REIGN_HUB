<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 18, 4)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
