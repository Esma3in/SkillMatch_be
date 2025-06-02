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
        Schema::create('roadmaps' ,function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('completed');
            $table->foreignId('candidate_id')->constrained();
            $table->foreignId("company_id")->nullable()->constrained('companies')->cascadeOnDelete();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Roadmaps');
        
    }
};
