<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LunchMenu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class LunchMenuController extends Controller
{
    /**
     * Display the lunch menu page or redirect to calendar.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        // Check if we should redirect to calendar
        if (!$request->has('show_all')) {
            return redirect()->route('portal.calendar', ['view' => 'lunch']);
        }

        // For admin view, show all menus
        $menus = LunchMenu::orderBy('menu_date', 'desc')
            ->paginate(30);

        return Inertia::render('Portal/Lunch/Index', [
            'menus' => $menus,
        ]);
    }

    /**
     * Show the form for creating a new lunch menu.
     */
    public function create(Request $request): Response
    {
        $this->authorizeAdmin();

        // Pre-fill date if provided (from calendar click)
        $menuDate = $request->get('date');

        return Inertia::render('Portal/Lunch/Create', [
            'prefilledDate' => $menuDate,
        ]);
    }

    /**
     * Store a newly created lunch menu.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'menu_date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        $menuDate = $this->normalizeMenuDate($validated['menu_date']);

        if (LunchMenu::forDate($menuDate)->exists()) {
            return back()
                ->withErrors(['menu_date' => 'A lunch menu already exists for this date.'])
                ->withInput();
        }

        LunchMenu::create([
            'user_id' => auth()->id(),
            'menu_date' => $menuDate,
            'content' => trim($validated['content']),
        ]);

        return redirect()->route('portal.calendar', ['view' => 'lunch'])
            ->with('success', 'Lunch menu created successfully.');
    }

    /**
     * Show the form for editing the specified lunch menu.
     */
    public function edit(LunchMenu $lunch): Response
    {
        $this->authorizeAdmin();

        return Inertia::render('Portal/Lunch/Edit', [
            'menu' => [
                'id' => $lunch->id,
                'menu_date' => $lunch->menu_date->format('Y-m-d'),
                'content' => $lunch->content,
            ],
        ]);
    }

    /**
     * Update the specified lunch menu.
     */
    public function update(Request $request, LunchMenu $lunch): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'menu_date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        $menuDate = $this->normalizeMenuDate($validated['menu_date']);
        $taken = LunchMenu::forDate($menuDate)->whereKeyNot($lunch->id)->exists();

        if ($taken) {
            return back()
                ->withErrors(['menu_date' => 'A lunch menu already exists for this date.'])
                ->withInput();
        }

        $lunch->update([
            'menu_date' => $menuDate,
            'content' => trim($validated['content']),
        ]);

        return redirect()->route('portal.calendar', ['view' => 'lunch'])
            ->with('success', 'Lunch menu updated successfully.');
    }

    /**
     * Remove the specified lunch menu.
     */
    public function destroy(LunchMenu $lunch): RedirectResponse
    {
        $this->authorizeAdmin();

        $lunch->delete();

        return redirect()->route('portal.calendar', ['view' => 'lunch'])
            ->with('success', 'Lunch menu deleted successfully.');
    }

    /**
     * Get menus for a specific month (JSON API for calendar).
     */
    public function getMenusForMonth(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $menus = LunchMenu::forMonth($year, $month)
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'menu_date' => $menu->menu_date->format('Y-m-d'),
                    'content' => $menu->content,
                    'day_name' => $menu->day_name,
                    'short_day_name' => $menu->short_day_name,
                    'formatted_date' => $menu->formatted_date,
                ];
            });

        return response()->json($menus);
    }

    /**
     * Keep the calendar day the user picked. A datetime string must not shift
     * that day into the previous evening when the app timezone is applied.
     */
    protected function normalizeMenuDate(string $value): string
    {
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', trim($value), $matches)) {
            return $matches[1];
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    /**
     * Check if current user is admin.
     */
    protected function authorizeAdmin(): void
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
