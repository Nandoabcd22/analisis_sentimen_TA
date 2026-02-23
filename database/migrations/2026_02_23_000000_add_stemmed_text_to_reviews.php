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
        Schema::table('reviews', function (Blueprint $table) {
            // Add stemmed_text column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'stemmed_text')) {
                $table->text('stemmed_text')->nullable()->after('stemming');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'stemmed_text')) {
                $table->dropColumn('stemmed_text');
            }
        });
    }
};
