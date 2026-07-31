<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipe_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // hitung, galvanis, dll
            $table->string('name');         // PIPA HITAM, PIPA GALVANIS, etc
            $table->timestamps();
        });

        Schema::create('pipe_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('size_label');     // 1/2", 3/4", 1", etc
            $table->integer('pcs_per_bundle'); // 217, 169, 127, etc
            $table->timestamps();
        });

        Schema::create('pipe_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // TIPIS, MEDIUM, TEBAL
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('pipe_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipe_size_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipe_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_per_bundle', 10, 2); // Weight in kg per bundle
            $table->timestamps();

            $table->unique(['pipe_size_id', 'pipe_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipe_weights');
        Schema::dropIfExists('pipe_types');
        Schema::dropIfExists('pipe_sizes');
        Schema::dropIfExists('pipe_categories');
    }
};
