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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('month');
            $table->string('year');
            $table->integer('days_worked')->default(0);
            $table->integer('regular_hours')->default(0);
            $table->integer('overtime_hours')->default(0);
            $table->integer('late_hours')->default(0);
            $table->integer('night_differential_hours')->default(0);
            $table->integer('regular_holiday_worked')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
