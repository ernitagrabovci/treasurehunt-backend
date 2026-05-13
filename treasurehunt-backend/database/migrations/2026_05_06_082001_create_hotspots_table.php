<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['nav', 'treasure']);
            $table->decimal('pitch', 6, 4);
            $table->decimal('yaw', 6, 4);
            $table->foreignId('target_scene_id')->nullable()->constrained('scenes')->onDelete('set null');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};
