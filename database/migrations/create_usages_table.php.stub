<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('feature');
            $table->decimal('used', 15, 4)->default(0);
            $table->decimal('limit', 15, 4)->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'feature']);
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usages');
    }
};
