<?php

namespace Tests\Feature;

use App\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLevelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_preschool_grade_levels_are_available(): void
    {
        $names = Grade::orderBy('sort_order')->pluck('name')->all();

        $this->assertSame('Early Learners (Preschool)', $names[0]);
        $this->assertSame('Ganeinu (Preschool)', $names[1]);
        $this->assertSame('Kindergarten', $names[2]);
    }
}
