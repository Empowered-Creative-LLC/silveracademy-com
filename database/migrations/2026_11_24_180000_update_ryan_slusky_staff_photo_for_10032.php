<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Point the existing Ryan Slusky staff_directory row at the portrait
     * added in public/images/updates/ for request #10032.
     *
     * Updates the photo path only, and only for an exact name match.
     * Does not insert rows or change names, titles, departments, or sort order.
     * Safe to re-run: the same path is written again.
     */
    public function up(): void
    {
        DB::table('staff_directory')->where('name', 'Ryan Slusky')->update([
            'photo' => '/images/updates/Ryan-Slusky.webp',
            'updated_at' => now(),
        ]);
    }

    /**
     * Restore the live 2026-11-24 path (no photo) for Ryan Slusky.
     */
    public function down(): void
    {
        DB::table('staff_directory')->where('name', 'Ryan Slusky')->update([
            'photo' => null,
            'updated_at' => now(),
        ]);
    }
};
