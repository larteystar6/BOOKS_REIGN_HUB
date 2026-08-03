<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->string('entity');
            $table->uuid('entity_id')->nullable();
            $table->enum('operation',['create','update','delete']);
            $table->json('payload');
            $table->enum('status',['pending','in_progress','done','failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
