<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('accounting.table_prefix', 'fincore_');

        Schema::create($prefix . 'posting_rules', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('jv_type')->default('general');
            $table->boolean('auto_post')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create($prefix . 'posting_rule_lines', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('posting_rule_id')
                ->constrained($prefix . 'posting_rules')
                ->cascadeOnDelete();
            $table->string('role');
            $table->string('label');
            $table->enum('type', ['debit', 'credit']);
            $table->string('amount_key');
            $table->foreignId('account_id')
                ->nullable()
                ->constrained($prefix . 'accounts')
                ->nullOnDelete();
            $table->string('default_code')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('accounting.table_prefix', 'fincore_');
        Schema::dropIfExists($prefix . 'posting_rule_lines');
        Schema::dropIfExists($prefix . 'posting_rules');
    }
};
