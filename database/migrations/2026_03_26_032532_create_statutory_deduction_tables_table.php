<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statutory_deduction_tables', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->json('rules');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_deduction_tables');
    }
};
