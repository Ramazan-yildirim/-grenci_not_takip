<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ogrenci_id')->constrained('ogrenciler')->cascadeOnDelete();
            $table->foreignId('ders_id')->constrained('dersler')->cascadeOnDelete();
            $table->decimal('not', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notlar');
    }
};
