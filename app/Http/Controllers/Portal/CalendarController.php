<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\LunchMenu;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display the calendar page.
     */
    public function index(Request $request): Response
    {
        // Get the default view from query parameter
        $defaultView = $request->get('view', 'events'); // events, lunch, or both
        
        // Define date range for recurring events (6 months before and after today)
        $rangeStart = Carbon::now()->subMonths(1)->startOfMonth();
        $rangeEnd = Carbon::now()->addMonths(12)->endOfMonth();

        // Get calendar events from the CalendarEvent model
        $calendarEvents = CalendarEvent::orderBy('event_date', 'asc')
            ->get();

        // Published events. Internal events are staff-only.
        $posts = Post::query()->events()->published();
        if (! $request->user()?->isStaff()) {
            $posts->where(function ($query) {
                $query->where('audience', 'all')->orWhereNull('audience');
            });
        }
        $posts = $posts->get();

        // Generate event instances (including recurring)
        $postEvents = collect();
        
        foreach ($posts as $post) {
            if ($post->isRecurring()) {
                // Generate all occurrences for recurring events
                $occurrences = $post->getOccurrences($rangeStart, $rangeEnd);
                $occurrenceIndex = 0;
                
                foreach ($occurrences as $occurrence) {
                    $eastern = Carbon::parse($occurrence)->timezone(config('app.timezone'));
                    $postEvents->push([
                        'id' => 'post-' . $post->id . '-' . $occurrenceIndex,
                        'original_id' => $post->id,
                        'title' => $post->title,
                        'event_date' => $eastern->format('Y-m-d\TH:i:s'),
                        'event_date_key' => $eastern->format('Y-m-d'),
                        'event_end_date' => $post->event_end_date
                            ? $post->event_end_date->copy()->timezone(config('app.timezone'))->format('Y-m-d\TH:i:s')
                            : null,
                        'event_end_date_key' => $post->event_end_date
                            ? $post->event_end_date->copy()->timezone(config('app.timezone'))->format('Y-m-d')
                            : null,
                        'description' => $post->content,
                        'button_text' => $post->button_text,
                        'button_url' => $post->button_url,
                        'recurrence_type' => $post->recurrence_type,
                        'is_recurring' => true,
                        'is_school_closure' => $post->is_school_closure,
                        'type' => 'event',
                    ]);
                    $occurrenceIndex++;
                }
            } else {
                $start = $post->event_start_date
                    ? $post->event_start_date->copy()->timezone(config('app.timezone'))
                    : null;
                $end = $post->event_end_date
                    ? $post->event_end_date->copy()->timezone(config('app.timezone'))
                    : null;
                $postEvents->push([
                    'id' => 'post-' . $post->id,
                    'original_id' => $post->id,
                    'title' => $post->title,
                    'event_date' => $start?->format('Y-m-d\TH:i:s'),
                    'event_date_key' => $start?->format('Y-m-d'),
                    'event_end_date' => $end?->format('Y-m-d\TH:i:s'),
                    'event_end_date_key' => $end?->format('Y-m-d'),
                    'description' => $post->content,
                    'button_text' => $post->button_text,
                    'button_url' => $post->button_url,
                    'recurrence_type' => 'none',
                    'is_recurring' => false,
                    'is_school_closure' => $post->is_school_closure,
                    'type' => 'event',
                ]);
            }
        }

        // Merge calendar events with post events
        $events = $calendarEvents->map(function ($event) {
            $start = $event->event_date
                ? $event->event_date->copy()->timezone(config('app.timezone'))
                : null;
            $end = $event->event_end_date
                ? $event->event_end_date->copy()->timezone(config('app.timezone'))
                : null;

            return [
                'id' => $event->id,
                'title' => $event->title,
                'event_date' => $start?->format('Y-m-d\TH:i:s'),
                'event_date_key' => $start?->format('Y-m-d'),
                'event_end_date' => $end?->format('Y-m-d\TH:i:s'),
                'description' => $event->description,
                'recurrence_type' => 'none',
                'is_recurring' => false,
                'type' => 'calendar',
            ];
        })->concat($postEvents);

        // Get lunch menus (using new menu_date field)
        $lunchMenus = LunchMenu::orderBy('menu_date', 'asc')
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

        // Get user for role-based UI
        $user = auth()->user();

        return Inertia::render('Portal/Calendar', [
            'events' => $events,
            'lunchMenus' => $lunchMenus,
            'defaultView' => $defaultView,
            'user' => $user,
        ]);
    }
}
