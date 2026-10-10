<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. PR-2026-08
            $table->string('title')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');
            $table->string('frequency')->default('monthly'); // monthly, fortnightly, weekly
            $table->string('status')->default('draft'); // draft, processing, calculated, approved, paid, cancelled
            $table->unsignedInteger('employee_count')->default(0);
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_employee_fnpf', 15, 2)->default(0);
            $table->decimal('total_employer_fnpf', 15, 2)->default(0);
            $table->decimal('total_paye', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->decimal('total_employer_cost', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
