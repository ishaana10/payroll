<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete(); // assumes employees table exists
            $table->string('employee_name');
            $table->string('employee_number')->nullable();
            $table->string('tin')->nullable();
            $table->string('fnpf_number')->nullable();
            $table->string('tax_code')->default('P'); // P = primary, S = secondary
            $table->boolean('is_resident')->default(true);

            // Earnings
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('overtime', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('other_earnings', 12, 2)->default(0);
            $table->decimal('gross', 12, 2)->default(0);

            // Statutory
            $table->decimal('fnpf_base', 12, 2)->default(0);
            $table->decimal('employee_fnpf', 12, 2)->default(0);
            $table->decimal('employer_fnpf', 12, 2)->default(0);
            $table->decimal('taxable_income', 12, 2)->default(0);
            $table->decimal('paye', 12, 2)->default(0);

            // Other
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);

            // Employer cost extras
            $table->decimal('workcare_levy', 12, 2)->default(0);
            $table->decimal('training_levy', 12, 2)->default(0);
            $table->decimal('employer_cost', 12, 2)->default(0);

            // Rates snapshot (for audit)
            $table->decimal('employee_fnpf_rate', 5, 4)->default(0.08);
            $table->decimal('employer_fnpf_rate', 5, 4)->default(0.08);

            $table->json('calculation_details')->nullable(); // full breakdown
            $table->string('status')->default('calculated');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
    }
};
