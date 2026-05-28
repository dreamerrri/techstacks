<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birthdate');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Separated']);
            $table->text('address');
            $table->string('contact_number');
            $table->string('email')->unique();

            // Employment Details
            $table->string('department');
            $table->string('position');
            $table->enum('employment_status', ['Regular', 'Probationary', 'Contractual', 'Part-time']);
            $table->date('date_hired');
            $table->enum('salary_type', ['Monthly', 'Daily', 'Hourly']);
            $table->decimal('basic_salary', 10, 2);

            // Government Contributions
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();

            // Soft archive
            $table->boolean('is_archived')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};