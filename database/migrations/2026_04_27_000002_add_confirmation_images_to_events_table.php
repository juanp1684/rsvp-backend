<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('confirm_attending_image')->nullable()->after('no_kids_message');
            $table->string('confirm_declined_image')->nullable()->after('confirm_attending_image');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['confirm_attending_image', 'confirm_declined_image']);
        });
    }
};
