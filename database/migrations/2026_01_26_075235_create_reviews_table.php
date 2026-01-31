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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->text('review');
            $table->string('label'); // Positif, Negatif, Netral
            $table->text('case_folding')->nullable();
            $table->text('cleansing')->nullable();
            $table->text('normalisasi')->nullable();
            $table->text('tokenizing')->nullable();
            $table->text('stopword')->nullable();
            $table->text('stemming')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
