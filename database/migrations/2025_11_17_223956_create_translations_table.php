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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade'); // null = system translation
            $table->string('locale'); // fr, ar, en
            $table->string('group')->default('*'); // validation, messages, ui, etc.
            $table->string('key'); // "invoice.number", "total_ht", etc.
            $table->text('value'); // Translated text
            $table->timestamps();

            $table->unique(['tenant_id', 'locale', 'group', 'key']);
            $table->index(['locale', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
