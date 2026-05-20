<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->string('unique_code', 8)->nullable()->after('status');
            $table->unique(['event_id', 'unique_code']);
        });
    }

    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'unique_code']);
            $table->dropColumn('unique_code');
        });
    }
};
