<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            if (!Schema::hasColumn('bookings', 'movie_title')) {
                $table->string('movie_title')->nullable()->after('showtime_id');
            }
            if (!Schema::hasColumn('bookings', 'show_time')) {
                $table->timestamp('show_time')->nullable()->after('movie_title');
            }
            if (!Schema::hasColumn('bookings', 'date')) {
                $table->timestamp('date')->nullable()->after('show_time');
            }
            if (!Schema::hasColumn('bookings', 'code')) {
                $table->string('code')->nullable()->after('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            
            if (Schema::hasColumn('bookings', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('bookings', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('bookings', 'show_time')) {
                $table->dropColumn('show_time');
            }
            if (Schema::hasColumn('bookings', 'movie_title')) {
                $table->dropColumn('movie_title');
            }
        });
    }
};
