<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('subtitle')->nullable();
            $table->string('dress_code_image')->nullable();
            $table->text('gift_suggestion')->nullable();
            $table->string('gift_suggestion_image')->nullable();
            $table->text('recommendations')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'dress_code_image', 'gift_suggestion', 'gift_suggestion_image', 'recommendations']);
        });
    }
};
