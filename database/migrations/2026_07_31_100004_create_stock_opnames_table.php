<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('petugas_name')->default('Petugas');
            $table->foreignId('block_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipe_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pipe_size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pipe_type_id')->nullable()->constrained()->nullOnDelete();
            
            // Sisi Kiri (Left side)
            $table->integer('left_bdl_per_row')->default(0);
            $table->integer('left_rows')->default(0);
            $table->integer('left_adjust')->default(0);
            $table->integer('left_bundles')->default(0);
            $table->integer('left_loose')->default(0);

            // Sisi Kanan (Right side)
            $table->integer('right_bdl_per_row')->default(0);
            $table->integer('right_rows')->default(0);
            $table->integer('right_adjust')->default(0);
            $table->integer('right_bundles')->default(0);
            $table->integer('right_loose')->default(0);

            // Calculated Totals
            $table->integer('total_bundles')->default(0);
            $table->integer('total_pcs')->default(0);
            $table->integer('total_loose')->default(0);
            $table->decimal('total_weight', 12, 2)->default(0);

            $table->date('opname_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
