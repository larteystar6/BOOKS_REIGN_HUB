<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('product_uuid');
            $table->string('filename');
            $table->string('storage_path');
            $table->string('thumb_path');
            $table->boolean('is_primary')->default(false);
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('filesize')->default(0);
            $table->string('hash', 64);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('cascade');
            $table->index('hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
