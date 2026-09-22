<?php

namespace Tests\Feature;

use App\Exports\LunchMenuTemplateExport;
use App\Models\LunchMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class LunchMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_menu_shows_it_on_that_calendar_day(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        $this->actingAs($admin)->post('/portal/lunch', [
            'menu_date' => '2026-09-11',
            'content' => 'Pizza Day',
        ])->assertRedirect(route('portal.calendar', ['view' => 'lunch']));

        $menu = LunchMenu::first();
        $this->assertNotNull($menu);
        $this->assertSame('2026-09-11', $menu->menu_date->format('Y-m-d'));

        $calendar = $this->actingAs($admin)
            ->withHeaders($this->inertiaHeaders())
            ->get('/portal/calendar?view=lunch');

        $calendar->assertOk();
        $menus = collect($calendar->json('props.lunchMenus'));
        $saved = $menus->firstWhere('content', 'Pizza Day');

        $this->assertNotNull($saved);
        $this->assertSame('2026-09-11', $saved['menu_date']);
    }

    public function test_menu_date_keeps_the_calendar_day_from_a_datetime_string(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        $this->actingAs($admin)->post('/portal/lunch', [
            'menu_date' => '2026-09-11T00:00:00.000000Z',
            'content' => 'Tacos',
        ])->assertRedirect(route('portal.calendar', ['view' => 'lunch']));

        $this->assertSame('2026-09-11', LunchMenu::first()->menu_date->format('Y-m-d'));
    }

    public function test_template_import_creates_weekday_menus(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        Excel::store(new LunchMenuTemplateExport, 'testing-lunch-template.xlsx', 'local');
        $full = storage_path('app/private/testing-lunch-template.xlsx');

        $response = $this->actingAs($admin)->post('/portal/lunch/import', [
            'file' => new UploadedFile($full, 'lunch-menu-template.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ]);

        if (file_exists($full)) {
            unlink($full);
        }

        $response->assertRedirect(route('portal.calendar', ['view' => 'lunch']));

        $calendar = $this->actingAs($admin)
            ->withHeaders($this->inertiaHeaders())
            ->get(route('portal.calendar', ['view' => 'lunch']));

        $calendar->assertOk();
        $this->assertGreaterThan(0, count($calendar->json('props.lunchMenus')));
        $this->assertStringContainsString('Import completed', (string) $calendar->json('props.flash.success'));
    }

    public function test_excel_date_cells_import_without_error(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        $sheet = new Spreadsheet();
        $rows = $sheet->getActiveSheet();
        $rows->setTitle('Lunch Menus');
        $rows->fromArray(['Date', 'Day of Week', 'Menu Content'], null, 'A1');
        $rows->setCellValue('A2', ExcelDate::PHPToExcel(new \DateTime('2026-09-11')));
        $rows->getStyle('A2')->getNumberFormat()->setFormatCode('mm/dd/yyyy');
        $rows->setCellValue('B2', 'Friday');
        $rows->setCellValue('C2', 'Pizza Day');

        $instructions = $sheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->setCellValue('A1', 'Lunch Menu Import Instructions');
        $instructions->setCellValue('A4', 'Date');
        $instructions->setCellValue('B4', 'Required. Example 01/15/2025');

        $full = storage_path('app/private/testing-lunch-dates.xlsx');
        (new Xlsx($sheet))->save($full);

        $response = $this->actingAs($admin)->post('/portal/lunch/import', [
            'file' => new UploadedFile($full, 'lunch-dates.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
        ]);

        @unlink($full);

        $response->assertRedirect();
        $this->assertSame(1, LunchMenu::count(), 'Excel date import session: '.json_encode($response->getSession()->all()));
        $this->assertSame('2026-09-11', LunchMenu::first()->menu_date->format('Y-m-d'));
    }

    private function inertiaHeaders(): array
    {
        $version = app(\App\Http\Middleware\HandleInertiaRequests::class)
            ->version(request());

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) $version,
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }
}
