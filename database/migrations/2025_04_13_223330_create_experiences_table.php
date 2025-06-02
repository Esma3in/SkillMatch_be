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
        
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("candidate_profile_id");
            $table->foreign('candidate_profile_id')->references("id")->on('profile_candidates')->onDelete('cascade')->onUpdate('cascade');
            $table->string('experience');
            $table->string('location');
            $table->string('employement_type');
            $table->text('role');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
