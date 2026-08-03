<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->string('entity');
            $table->uuid('entity_id')->nullable();
            $table->string('action');
            $table->uuid('user_uuid')->nullable();
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
