<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_uuid', 191)->index();
            $table->string('name')->nullable();
            $table->string('platform')->nullable(); // android, ios, web
            $table->string('push_token')->nullable(); // Firebase token később
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('revoked')->default(false);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
