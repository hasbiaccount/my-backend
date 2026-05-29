<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('event_participants', 'cancelled_at')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->timestamp('cancelled_at')->nullable()->after('unique_code')->index();
            });
        }

        $this->changeStatusEnum(['registered', 'attended', 'absent', 'cancelled']);
    }

    public function down(): void
    {
        DB::table('event_participants')
            ->where('status', 'cancelled')
            ->update([
                'status' => 'registered',
                'cancelled_at' => null,
            ]);

        $this->changeStatusEnum(['registered', 'attended', 'absent']);

        if (Schema::hasColumn('event_participants', 'cancelled_at')) {
            Schema::table('event_participants', function (Blueprint $table) {
                $table->dropColumn('cancelled_at');
            });
        }
    }

    private function changeStatusEnum(array $statuses): void
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $enum = collect($statuses)
            ->map(fn (string $status) => DB::getPdo()->quote($status))
            ->implode(', ');

        DB::statement("ALTER TABLE event_participants MODIFY status ENUM({$enum}) NOT NULL DEFAULT 'registered'");
    }
};
