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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('email')->constrained('branches')->onDelete('set null');
            $table->boolean('active')->default(true)->after('branch_id'); // Status del usuario
            $table->softDeletes()->after('updated_at'); // Soft delete para usuarios
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'active']);
            $table->dropSoftDeletes();
        });
    }
};
