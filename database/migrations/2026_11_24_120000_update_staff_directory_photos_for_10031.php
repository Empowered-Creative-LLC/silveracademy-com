<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Point existing staff_directory rows at the optimized 3:4 portraits
     * added in public/images/updates/ for request #10031.
     *
     * Updates the photo path only, and only for exact name matches.
     * Does not insert rows or change names, titles, departments, or sort order.
     */
    public function up(): void
    {
        foreach ($this->photos() as $name => $path) {
            DB::table('staff_directory')->where('name', $name)->update([
                'photo' => $path,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Restore the photo paths that were live on 2026-11-24 before this change.
     */
    public function down(): void
    {
        foreach ($this->previousPhotos() as $name => $path) {
            DB::table('staff_directory')->where('name', $name)->update([
                'photo' => $path,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function photos(): array
    {
        return [
            'Allie Roberson' => '/images/updates/Allie-Roberson.webp',
            'Amy Cober' => '/images/updates/Amy-Cober.webp',
            'Hannah Bailey' => '/images/updates/Hannah-Bailey.webp',
            'Esti Ben-David' => '/images/updates/Esti-Ben-David.webp',
            'Linsey Shapiro' => '/images/updates/Linsey-Shapiro.webp',
            'Marina Cherepinsky' => '/images/updates/Marina-Cherepinsky.webp',
            'Jeff Hughes' => '/images/updates/Jeff-Hughes.webp',
            'Gila Ogle' => '/images/updates/Gila-Ogle.webp',
            'Brandy Hurley' => '/images/updates/Brandy-Hurley.webp',
            'Shiran Goldstein' => '/images/updates/Shiran-Goldstein.webp',
            'Rabbi Moshe Yosef Gewirtz' => '/images/updates/Rabbi-Moshe-Yosef-Gewirtz.webp',
            'Litaf Elias' => '/images/updates/Litaf-Elias.webp',
            'Alla Fligelman' => '/images/updates/Alla-Fligelman.webp',
            'Menashe Elias' => '/images/updates/Menashe-Elias.webp',
            'Rebecca Cortes' => '/images/updates/Rebecca-Cortes.webp',
            'Aviva Woodland' => '/images/updates/Aviva-Woodland.webp',
            'Bella Altman' => '/images/updates/Bella-Altman.webp',
            'Tamara Reid' => '/images/updates/Tamara-Reid.webp',
            'Rebecca Raffensperger' => '/images/updates/Rebecca-Raffensperger.webp',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function previousPhotos(): array
    {
        return [
            'Allie Roberson' => null,
            'Amy Cober' => '/img/graphics/staff/Amy Cober.png',
            'Hannah Bailey' => null,
            'Esti Ben-David' => null,
            'Linsey Shapiro' => null,
            'Marina Cherepinsky' => 'https://drive.google.com/file/d/1DwBZ9DZBfrmos7cTLdzyBPA0WOdA7oGX/view?usp=sharing',
            'Jeff Hughes' => '/img/graphics/staff/Jeff Hughes.png',
            'Gila Ogle' => '/img/graphics/staff/Gila Ogle.png',
            'Brandy Hurley' => '/img/graphics/staff/Brandy Hurley.png',
            'Shiran Goldstein' => '/img/graphics/staff/Shiran Goldstein.png',
            'Rabbi Moshe Yosef Gewirtz' => '/img/graphics/staff/Rabbi Moshe Yosef Gewirtz.png',
            'Litaf Elias' => '/img/graphics/staff/Litaf Elias.png',
            'Alla Fligelman' => '/img/graphics/staff/Alla Fligelman.png',
            'Menashe Elias' => '/img/graphics/staff/Menashe Elias.png',
            'Rebecca Cortes' => '/img/graphics/staff/Rebecca Cortes.png',
            'Aviva Woodland' => '/img/graphics/staff/Aviva Woodland.png',
            'Bella Altman' => '/img/graphics/staff/Bella Altman.png',
            'Tamara Reid' => '/img/graphics/staff/Tamara Reid.png',
            'Rebecca Raffensperger' => '/img/graphics/staff/Rebecca Raffensperger.png',
        ];
    }
};
