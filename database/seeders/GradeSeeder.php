<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = [
            ['name' => 'Early Childhood Program', 'sort_order' => 1],
            ['name' => 'Ganeinu (Preschool)', 'sort_order' => 2],
            ['name' => 'Kindergarten', 'sort_order' => 3],
            ['name' => '1st Grade', 'sort_order' => 4],
            ['name' => '2nd Grade', 'sort_order' => 5],
            ['name' => '3rd Grade', 'sort_order' => 6],
            ['name' => '4th Grade', 'sort_order' => 7],
            ['name' => '5th Grade', 'sort_order' => 8],
            ['name' => '6th Grade', 'sort_order' => 9],
            ['name' => '7th Grade', 'sort_order' => 10],
            ['name' => '8th Grade', 'sort_order' => 11],
        ];

        foreach ($grades as $grade) {
            Grade::updateOrCreate(
                ['name' => $grade['name']],
                $grade
            );
        }

        $this->command->info('Grade levels seeded successfully.');
    }
}



