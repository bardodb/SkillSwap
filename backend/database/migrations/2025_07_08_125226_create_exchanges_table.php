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
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('offered_skill_id')->constrained('skills')->onDelete('cascade');
            $table->foreignId('requested_skill_id')->constrained('skills')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'scheduled', 'completed', 'cancelled'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('rating_initiator')->nullable();
            $table->integer('rating_receiver')->nullable();
            $table->text('feedback_initiator')->nullable();
            $table->text('feedback_receiver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchanges');
    }
};
