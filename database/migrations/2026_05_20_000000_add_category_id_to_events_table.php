<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $column = $table->foreignId('category_id')
                ->nullable()
                ->after('organizer_id');

            if (DB::getDriverName() !== 'sqlite') {
                $column->constrained('categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn('category_id');
            } else {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
