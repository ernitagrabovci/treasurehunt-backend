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
        Schema::table('user_treasures', function (Blueprint $table) {
            $table->integer('time_spent_seconds')->nullable()->after('found_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_treasures', function (Blueprint $table) {
            $table->dropColumn('time_spent_seconds');
        });
    }
};
