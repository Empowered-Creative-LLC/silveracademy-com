<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // Plain token for this response. The document meta tag goes stale after
            // logout regenerates the session while Inertia stays on the same page.
            'csrf_token' => fn () => csrf_token(),
            'portal' => [
                'coming_soon' => (bool) config('portal.coming_soon'),
                'sandbox_enabled' => (bool) config('portal.sandbox_enabled') && ! app()->environment('production'),
                'sandbox_password' => ((bool) config('portal.sandbox_enabled') && ! app()->environment('production'))
                    ? (string) config('portal.sandbox_password', 'Sandbox123!')
                    : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
                'sandbox_password' => fn () => $request->session()->get('sandbox_password'),
                'sandbox_email' => fn () => $request->session()->get('sandbox_email'),
                'new_student_code_plain' => fn () => $request->session()->get('new_student_code_plain'),
                'new_student_name' => fn () => $request->session()->get('new_student_name'),
                'regenerated_code_plain' => fn () => $request->session()->get('regenerated_code_plain'),
                'regenerated_code_student_name' => fn () => $request->session()->get('regenerated_code_student_name'),
            ],
        ];
    }
}
