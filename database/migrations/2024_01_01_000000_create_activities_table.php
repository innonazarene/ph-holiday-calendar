<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPTIONAL stub migration.
 *
 * This is only needed if you don't already have an activities table.
 * The package works with any existing table — just set the correct
 * table name in config/calendar-activities.php.
 *
 * Run:  php artisan vendor:publish --tag=calendar-activities-migrations
 *       php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = config('calendar-activities.activities.table', 'activities');

        Schema::create($table, function (Blueprint $table) {
            $table->id();

            // ── Core fields (required by Activity DTO) ──────────────────
            $table->string('title');
            $table->date('date');
            $table->string('type')->default('institutional');
            // type values: institutional | departmental | personal

            // ── Optional fields ─────────────────────────────────────────
            $table->text('description')->nullable();
            $table->date('end_date')->nullable();
            $table->string('department')->nullable();
            $table->string('created_by')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('all_day')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // ── Add your own columns freely below ───────────────────────
            // e.g.: $table->foreignId('user_id')->constrained();
            //       $table->string('venue')->nullable();
            //       $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index('date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('calendar-activities.activities.table', 'activities'));
    }
};
