<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_flags', function (Blueprint $table) {
            $table->id();
            $table->boolean('restart')->default(false);
            $table->timestamps();
        });

        // 1 default rekord
        \DB::table('system_flags')->insert(['restart' => false]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_flags');
    }
};
