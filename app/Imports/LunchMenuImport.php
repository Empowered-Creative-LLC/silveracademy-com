<?php

namespace App\Imports;

use App\Models\LunchMenu;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class LunchMenuImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithEvents
{
    protected array $errors = [];
    protected int $created = 0;
    protected int $updated = 0;
    protected int $skipped = 0;
    protected int $userId;
    
    protected bool $skipSheet = false;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $title = strtolower(trim($event->getSheet()->getTitle()));
                $this->skipSheet = in_array($title, ['instructions', 'instruction'], true);
            },
        ];
    }

    public function model(array $row)
    {
        if ($this->skipSheet) {
            return null;
        }

        // Normalize headers (handle different variations)
        $date = $row['date'] ?? $row['menu_date'] ?? null;
        $content = $row['menu']
            ?? $row['menu_content']
            ?? $row['content']
            ?? $row['description']
            ?? $row['lunch']
            ?? $row['meal']
            ?? null;
        $dayOfWeek = $row['day_of_week'] ?? $row['day'] ?? null; // Optional, ignored for processing

        if (!$date || !$content) {
            $this->errors[] = "Missing required field(s). Date and Menu content are required.";
            $this->skipped++;
            return null;
        }

        // Parse the date (handles multiple formats)
        $menuDate = $this->parseDate($date);
        
        if (!$menuDate) {
            $this->errors[] = "Invalid date format: '{$date}'. Please use MM/DD/YYYY or YYYY-MM-DD format.";
            $this->skipped++;
            return null;
        }

        // Check if date is a weekend and skip if so
        if ($menuDate->isWeekend()) {
            $this->errors[] = "Warning: Skipping weekend date {$menuDate->format('M j, Y')} ({$menuDate->format('l')}).";
            $this->skipped++;
            return null;
        }

        $dateKey = $menuDate->format('Y-m-d');

        // Check for existing menu on this date
        $existingMenu = LunchMenu::forDate($dateKey)->first();

        if ($existingMenu) {
            $existingMenu->update([
                'content' => $content,
            ]);
            $this->updated++;

            return null;
        }

        LunchMenu::create([
            'user_id' => $this->userId,
            'menu_date' => $dateKey,
            'content' => $content,
        ]);

        $this->created++;

        // Already persisted. Returning the model makes the importer save it again,
        // and a second insert rolls the whole file back.
        return null;
    }

    /**
     * Parse various date formats.
     */
    protected function parseDate($date): ?Carbon
    {
        // If it's already a Carbon instance or DateTime
        if ($date instanceof Carbon) {
            return $date;
        }
        
        if ($date instanceof \DateTime) {
            return Carbon::instance($date);
        }

        // Handle Excel serial date number
        if (is_numeric($date)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            } catch (\Exception $e) {
                // Fall through to string parsing
            }
        }

        if (is_string($date)) {
            $date = trim($date);
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $date, $matches)) {
                return Carbon::createFromFormat('Y-m-d', $matches[1])->startOfDay();
            }
        }

        // Try common date formats
        $formats = [
            'Y-m-d',       // 2025-01-15
            'm/d/Y',       // 01/15/2025
            'n/j/Y',       // 1/15/2025
            'm-d-Y',       // 01-15-2025
            'd/m/Y',       // 15/01/2025 (European)
            'M j, Y',      // Jan 15, 2025
            'F j, Y',      // January 15, 2025
            'm/d/y',       // 01/15/25
        ];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, trim($date));
                if ($parsed && !$parsed->isValid()) {
                    continue;
                }
                if ($parsed) {
                    return $parsed->startOfDay();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Last resort: try Carbon's flexible parsing
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'date' => 'nullable',
            'menu' => 'nullable|string',
            'menu_content' => 'nullable|string',
            'content' => 'nullable|string',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            $this->skipped++;
        }
    }

    public function onError(Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        $this->skipped++;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStats(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }
}

