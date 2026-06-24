<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Migrations\AiMigration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    

    public function up(): void

    {
        Schema::table('agent_conversations', function (Blueprint $table) {
            $table->foreignId('generated_post_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_conversations', function (Blueprint $table) {
            $table->dropForeign(['generated_post_id']); // supprime la contrainte FK d'abord
            $table->dropColumn('generated_post_id');    // puis la colonne
        });
    }
};
