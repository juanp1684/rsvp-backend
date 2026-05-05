<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('companions')->whereNull('full_name')->orWhere('full_name', '')->update(['full_name' => 'Sin nombre']);

        Schema::table('companions', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('companions', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
        });
    }
};
