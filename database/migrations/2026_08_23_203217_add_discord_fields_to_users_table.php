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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'discord_id')) {
                $table->string('discord_id')->nullable()->unique()->after('mail');
            }
            if (!Schema::hasColumn('users', 'discord_username')) {
                $table->string('discord_username')->nullable()->after('discord_id');
            }
            if (!Schema::hasColumn('users', 'discord_avatar')) {
                $table->string('discord_avatar')->nullable()->after('discord_username');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['discord_id', 'discord_username', 'discord_avatar']);
        });
    }
};