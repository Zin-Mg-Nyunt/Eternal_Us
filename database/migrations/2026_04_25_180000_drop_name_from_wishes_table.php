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
        if (! Schema::hasTable('wishes') || ! Schema::hasColumn('wishes', 'name')) {
            return;
        }

        Schema::table('wishes', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('wishes') || Schema::hasColumn('wishes', 'name')) {
            return;
        }

        Schema::table('wishes', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });
    }
};
