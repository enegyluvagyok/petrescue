<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_builds', function (Blueprint $t) {
            $t->id();
            $t->string('file_name');
            $t->string('original_name');
            $t->string('version')->nullable();
            $t->string('build_type')->default('release'); // pl. release / debug
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('app_builds');
    }
};
