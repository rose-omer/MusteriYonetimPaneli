<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "activities" tablosunu kod kullanmıyordu; tek faaliyet tablosu "faaliyets".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('activities');
    }

    public function down(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }
};
