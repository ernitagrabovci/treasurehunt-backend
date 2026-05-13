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
        Schema::table('hotspots', function (Blueprint $table) {
            $table->decimal('pitch', 8, 4)->change();
            $table->decimal('yaw', 8, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->decimal('pitch', 6, 4)->change();
            $table->decimal('yaw', 6, 4)->change();
        });
    }
};
