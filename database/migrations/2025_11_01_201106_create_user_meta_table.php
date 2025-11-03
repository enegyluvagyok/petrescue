<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('birth_place')->nullable(); // magyar település
            $table->string('mother_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable(); // magyar település
            $table->string('street_name')->nullable();
            $table->string('street_type')->nullable(); // pl. utca, tér, út stb.
            $table->string('house_number')->nullable();
            $table->string('floor')->nullable();
            $table->string('door')->nullable();
            $table->string('id_card_number', 20)->nullable();
            $table->string('taj_number', 20)->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('avatar_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_meta');
    }
};
