<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create pipe_classes table
        Schema::create('pipe_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SCH40, L, M, BSA, MED
            $table->string('name');           // SCH 40, L, M, BSA, MED
            $table->timestamps();
        });

        // Add pipe_class_id to stock_opnames
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->foreignId('pipe_class_id')->nullable()->after('pipe_type_id')->constrained()->nullOnDelete();
            $table->date('input_date')->nullable()->after('opname_date')
                  ->comment('Date when this record was entered, for daily grouping');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['pipe_class_id']);
            $table->dropColumn(['pipe_class_id', 'input_date']);
        });
        Schema::dropIfExists('pipe_classes');
    }
};
