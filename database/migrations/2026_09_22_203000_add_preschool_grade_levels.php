<?php

use App\Models\Grade;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $renamed = Grade::where('name', 'Early Learning Program')->first();
        if ($renamed && ! Grade::where('name', 'Early Learners (Preschool)')->exists()) {
            $renamed->update(['name' => 'Early Learners (Preschool)']);
        }

        $grades = [
            ['name' => 'Early Learners (Preschool)', 'sort_order' => 1],
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
    }

    public function down(): void
    {
        Grade::where('name', 'Early Learners (Preschool)')
            ->whereDoesntHave('students')
            ->delete();
    }
};
